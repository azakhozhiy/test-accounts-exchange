<?php

namespace App\DTO;

class AcceptOrderDTO
{
    public function __construct(
        protected string $accountGiveAcceptorId,
        protected string $accountReceiveAcceptorId
    )
    {

    }

    public static function createFromArray(array $data): static
    {
        return new static(
            $data['account_give_acceptor_id'],
            $data['account_receive_acceptor_id']
        );
    }

    public function getAccountGiveAcceptorId(): string
    {
        return $this->accountGiveAcceptorId;
    }

    public function getAccountReceiveAcceptorId(): string
    {
        return $this->accountReceiveAcceptorId;
    }
}
