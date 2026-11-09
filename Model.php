<?php
/** Kelas dasar semua Model: pembungkus query PDO. */
abstract class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    protected function fetchAll($sql, array $params = [])
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    protected function fetchOne($sql, array $params = [])
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    protected function fetchValue($sql, array $params = [])
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }

    protected function execute($sql, array $params = [])
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }
}
