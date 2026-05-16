<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountingJournal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    /**
     * GET /api/account
     * Retourne le compte et l'historique des transactions.
     */
    public function show(Request $request): JsonResponse
    {
        $user    = $request->user();
        $account = $user->account;

        if (! $account) {
            return response()->json(['message' => 'Aucun compte trouvé.'], 404);
        }

        $transactions = $account->transactions()
            ->orderBy('date', 'desc')
            ->get(['id', 'account_id', 'type', 'amount', 'date']);

        return response()->json([
            'account'      => $account->only(['id', 'user_id', 'balance']),
            'transactions' => $transactions,
        ]);
    }

    /**
     * POST /api/account/credit
     * Crédite le compte de l'utilisateur.
     */
    public function credit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Montant invalide.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $account = $request->user()->account;

        if (! $account) {
            return response()->json(['message' => 'Aucun compte trouvé.'], 404);
        }

        DB::transaction(function () use ($account, $request) {
            $account->increment('balance', $request->amount);

            AccountingJournal::create([
                'account_id' => $account->id,
                'type'       => 'credit',
                'amount'     => $request->amount,
                'date'       => now(),
            ]);
        });

        $account->refresh();
        $transactions = $account->transactions()
            ->orderBy('date', 'desc')
            ->get(['id', 'account_id', 'type', 'amount', 'date']);

        return response()->json([
            'message'      => 'Compte crédité avec succès.',
            'account'      => $account->only(['id', 'user_id', 'balance']),
            'transactions' => $transactions,
        ]);
    }

    /**
     * POST /api/account/debit
     * Débite le compte de l'utilisateur.
     */
    public function debit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Montant invalide.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $account = $request->user()->account;

        if (! $account) {
            return response()->json(['message' => 'Aucun compte trouvé.'], 404);
        }

        if ($request->amount > $account->balance) {
            return response()->json([
                'message' => 'Solde insuffisant. Solde actuel : ' . $account->balance . ' XAF.',
            ], 422);
        }

        DB::transaction(function () use ($account, $request) {
            $account->decrement('balance', $request->amount);

            AccountingJournal::create([
                'account_id' => $account->id,
                'type'       => 'debit',
                'amount'     => $request->amount,
                'date'       => now(),
            ]);
        });

        $account->refresh();
        $transactions = $account->transactions()
            ->orderBy('date', 'desc')
            ->get(['id', 'account_id', 'type', 'amount', 'date']);

        return response()->json([
            'message'      => 'Compte débité avec succès.',
            'account'      => $account->only(['id', 'user_id', 'balance']),
            'transactions' => $transactions,
        ]);
    }
}
