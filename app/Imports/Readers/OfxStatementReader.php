<?php

namespace App\Imports\Readers;

use App\Imports\Contracts\StatementReader;
use App\Imports\DecimalAmount;
use App\Imports\StatementTransaction;
use App\Imports\InstallmentFromDescription;
use App\Imports\TextEncoding;
use App\Imports\Ofx\OfxDate;
use App\Imports\Ofx\OfxTags;
use App\Imports\TransactionDirection;

final class OfxStatementReader implements StatementReader
{
    private const TRANSACTION_TAG = 'STMTTRN';
    private const FALLBACK_DESCRIPTION = 'Lançamento sem descrição';

    public function __construct(
        private readonly TextEncoding $charset,
        private readonly OfxTags $tags,
        private readonly OfxDate $date,
        private readonly DecimalAmount $amount,
        private readonly InstallmentFromDescription $installments,
    ) {}

    public function source(): string
    {
        return "ofx";
    }

    public function supports(string $contents, string $extension): bool
    {
        return strtolower($extension) === 'ofx'
            || str_contains(strtoupper(substr($contents, 0, 1024)), 'OFXHEADER')
            || str_contains(strtoupper(substr($contents, 0, 1024)), '<OFX>');
    }

    /**
     * @return array<int, StatementTransaction>
     */
    public function read(string $contents): array
    {
        $decoded = $this->charset->toUtf8($contents);

        $transactions = array_map(
            fn (string $block) => $this->toTransaction($block),
            $this->tags->blocks($decoded, self::TRANSACTION_TAG)
        );

        return array_values(array_filter($transactions));
    }

    private function toTransaction(string $block): ?StatementTransaction
    {
        $occurredAt = $this->date->parse($this->tags->value($block, 'DTPOSTED', 'DTUSER') ?? '');
        $signedAmount = $this->amount->normalize($this->tags->value($block, 'TRNAMT') ?? '');

        if ($occurredAt === null || $signedAmount === null) {
            return null;
        }

        $description = $this->tags->value($block, 'MEMO', 'NAME') ?? self::FALLBACK_DESCRIPTION;
        $installment = $this->installments->extract($description);

        return new StatementTransaction(
            occurredAt: $occurredAt,
            description: $description,
            amount: ltrim($signedAmount, '-'),
            direction: TransactionDirection::fromSignedAmount($signedAmount),
            externalId: $this->tags->value($block, 'FITID'),
            installmentNumber: $installment[0] ?? null,
            totalInstallments: $installment[1] ?? null,
        );
    }
}
