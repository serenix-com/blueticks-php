<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Account;

final class AccountResource extends BaseResource
{
    /**
     * Retrieve the authenticated account.
     *
     * Returns the account associated with the API key used for this request.
     */
    public function retrieve(): Account
    {
        $data = $this->client->request('GET', '/v1/account');
        return Account::fromArray($data);
    }
}
