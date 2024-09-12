<?php

namespace App\DTO;

class CreateExchangeOrderDTO
{
    public static function createFromArray(array $data): static
    {
        $give = new AmountDTO($data['give']['amount'], $data['give']['currency']);
        $receive = new AmountDTO($data['receive']['amount'], $data['receive']['currency']);
        $accountGiveId = $data['account_give_id'];
        $accountReceiveId = $data['account_receive_id'];

        return new static($give, $receive, $accountGiveId, $accountReceiveId);
    }

    public function __construct(
        protected AmountDTO $give,
        protected AmountDTO $receive,
        protected string    $accountGiveId,
        protected string    $accountReceiveId
    )
    {

    }

    public function getAccountGiveId(): string
    {
        return $this->accountGiveId;
    }

    public function getAccountReceiveId(): string
    {
        return $this->accountReceiveId;
    }

    public function getGive(): AmountDTO
    {
        return $this->give;
    }

    public function getReceive(): AmountDTO
    {
        return $this->receive;
    }
}
