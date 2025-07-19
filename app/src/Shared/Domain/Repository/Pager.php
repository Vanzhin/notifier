<?php

declare(strict_types=1);

namespace App\Shared\Domain\Repository;

class Pager
{
    public const int DEFAULT_LIMIT = 10;
    public const int MAX_LIMIT = 100;
    public const int DEFAULT_PAGE = 1;

    public int $total_pages = 0;
    public int $per_page;

    /**
     * @throws \Exception
     */
    public function __construct(
        public int  $page,
        int         $per_page,
        public ?int $total_items = null
    )
    {
        $this->checkPage();
        $this->setLimit($per_page);
        $this->setTotalPages();
    }

    public static function emptySet(): self
    {
        return new self(1, 0);
    }

    public static function fromPage(?int $page, ?int $perPage): self
    {
        return new self($page ?? self::DEFAULT_PAGE, $perPage ?? self::DEFAULT_LIMIT);
    }

    public function getOffset(): int
    {
        if (1 === $this->page) {
            return 0;
        }

        return $this->page * $this->per_page - $this->per_page;
    }

    public function getLimit(): int
    {
        return $this->per_page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    private function setTotalPages(): void
    {
        if (!$this->total_items) {
            $this->total_pages = 0;
        } else {
            $this->total_pages = (int)ceil($this->total_items / $this->per_page);
        }
    }

    private function setLimit(int $per_page): void
    {
        $this->per_page = $per_page <= static::MAX_LIMIT ? $per_page : static::DEFAULT_LIMIT;
    }

    /**
     * @throws \Exception
     */
    private function checkPage(): void
    {
        if ($this->page <= 0) {
            throw new \Exception('Страница должна быть положительным числом.');
        }
    }
}
