<?php

namespace Amelia\Monzo\Api;

use Amelia\Monzo\Models\Transaction;

trait Transactions
{
    /**
     * Get a list of transactions for the current user.
     *
     * @param string|null $account
     * @return \Amelia\Monzo\Models\Transaction[]|\Illuminate\Support\Collection
     */
    public function transactions(string $account = null)
    {
        $results = $this->withErrorHandling(function () use ($account) {
            if ($account === null) {
                // best-effort to find an existing account.

                $account = $this->findExistingAccount();
            }

            return $this->client
                ->token($this->getAccessToken())
                ->call('GET', 'transactions', 'transactions', ['account_id' => $account]);
        });

        return collect($results)->map(function ($item) {
            return new Transaction($item);
        });
    }

    /**
     * Get a single transaction for a given ID.
     *
     * @param string $id
     * @return \Amelia\Monzo\Models\Transaction
     */
    public function transaction(string $id)
    {
        $results = $this->withErrorHandling(function () use ($id) {
            $this->expand('merchant');

            return $this->client
                ->token($this->getAccessToken())
                ->call('GET', "transactions/{$id}", 'transaction');
        });

        return new Transaction($results);
    }
}
