<?php

namespace Cherry\Repository;

use Doctrine\DBAL\Connection;

class NewsRepository
{
    const DB_TABLE_NAME = 'news';

    /** @var  Connection */
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function find(int $id): array
    {
        $sql = "SELECT * FROM `news` WHERE id = ?";

        return $this->db->fetchAssoc($sql, [$id]);
    }

    public function findOneBy(int $id): array
    {
        throw new \Exception('Not implemented');
    }

    public function findAll(): array
    {
        return $this->db->fetchAll('SELECT * FROM `news` ORDER BY `date_unix` DESC');
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): array
    {
        throw new \Exception('Not implemented');
    }

    public function update(array $data, array $identifier): int
    {
        return $this->db->update('news', $data, $identifier);
    }

    public function insert(array $data): int
    {
        return $this->db->insert('news', $data);
    }

    public function findArtWorks($id)
    {
        $sql = "SELECT * FROM `news_art_works`CROSS JOIN art_works on news_art_works.art_works_id = art_works.id  WHERE news_id = ? ";

        return $this->db->fetchAll($sql, [$id]);
    }

    public function setArtWorks($id, array $artWorks)
    {
        $artWorksIds = array_map([$this, 'getArtWorksIds'], $artWorks);
        $existingArtWorksIds = array_map([$this, 'getArtWorksIds'], $this->findArtWorks($id));

        $newItems = array_diff($artWorksIds, $existingArtWorksIds);
        array_map(function ($artWorkId) use ($id) {
            $this->addArtWork($id, $artWorkId);
        }, $newItems);

        $deletedItems = array_diff($existingArtWorksIds, $artWorksIds);
        array_map(function ($artWorkId) use ($id) {
            $this->removeArtWork($id, $artWorkId);
        }, $deletedItems);
    }

    public function addArtWork($newsId, $artWorkId)
    {
        $this->db->insert('news_art_works', ['news_id' => $newsId, 'art_works_id' => $artWorkId]);
    }

    public function removeArtWork($newsId, $artWorkId)
    {
        $this->db->delete('news_art_works', ['news_id' => $newsId, 'art_works_id' => $artWorkId]);
    }

    public static function getArtWorksIds($item)
    {
        if (is_object($item)) {
            return $item->id;
        }

        return $item['id'];
    }
}
