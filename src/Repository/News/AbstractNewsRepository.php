<?php

namespace App\Repository\News;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;

class AbstractNewsRepository
{
    protected string $repositoryName = 'abstract';
    protected int $pageNumber = 1;
    protected int $onPageCount = 20;
    protected int $expiresTime = 3600; // 1 hour

    public function __construct(
        protected CacheInterface $cache,
        protected LockFactory    $lockFactory
    ) {
    }

    /**
     * Устанавливаем параметры класса
     * @param int $pageNumber
     * @param int|null $onPageCount
     * @param string|null $repositoryName
     * @param int|null $expiresTime
     * @return void
     */
    public function setParams(
        int    $pageNumber,
        int    $onPageCount = null,
        string $repositoryName = null,
        int    $expiresTime = null
    ): void {
        $this->pageNumber = $pageNumber;
        $this->onPageCount = $onPageCount;
        $this->repositoryName = $repositoryName ?? $this->repositoryName;
        $this->expiresTime = $repositoryName ?? $this->expiresTime;
    }

    /**
     * Получаем список записей, если данных нет в кэше запускаем процесс генерации кэша
     * @return array
     * @throws InvalidArgumentException
     */
    public function getRecords(): array {
        $offset = $this->getRecordsOffset();
        $cacheKey = "{$this->repositoryName}:page:{$this->pageNumber}";
        // Получаем с базы список ID для нужной страницы
        $idList = $this->cache->get($cacheKey, function (CacheItemInterface $cacheItem) {
            $cacheItem->expiresAfter($this->expiresTime);
            return [];
        });
        // Затем проверяем данные по каким ID есть в кэше
        $lock = $this->lockFactory->createLock($cacheKey);
        $lock->acquire(true);
        $recs = $this->loadDataFromCache($idList);
        $lock->release();
        return $recs;
    }

    protected function getRecordsOffset(): string {
        return
            (!empty($this->onPageCount) ? ($this->pageNumber - 1) * $this->onPageCount : 0)
            . ", " .
            $this->onPageCount ?? 9999;
    }

    /**
     * Считываем информацию по полученным ID
     * Если каких-то не хватает в кэше, формируем этот список и получаем его отдельно
     * @throws InvalidArgumentException
     */
    protected function loadDataFromCache(array $idList): array {
        $notIntCache = [];
        $result = [];
        foreach ($idList as $id) {
            $item = $this->cache->get("news_record:{$id}", function () {
                return null;
            });
            if (empty($item)) $notIntCache[] = $id;
            $result[$id] = $item;
        }
        if (!empty($notIntCache)) $result = $this->getRecordsFromBase($notIntCache, $result);
        return $result;
    }

    protected function getRecordsFromBase(array $notIntCache, array $result): array {
        $idList = implode(',', $notIntCache);
        //$recs = Тут получаем записи с базы, обрабатываем отдельным сервисом и вуаля
        return $result;
    }
}