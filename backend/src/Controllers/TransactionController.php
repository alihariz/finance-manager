<?php
namespace App\Controllers;

use App\db;

class TransactionController
{
    private $db;

    public function __construct(db $db)
    {
        $this->db = $db;
    }

    public function index($user_id)
    {
        $stmt = $this->db->getPDO()->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public function getById($user_id, $id)
    {
        $stmt = $this->db->getPDO()->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch();
    }

    public function store($user_id, $data)
    {
        $stmt = $this->db->getPDO()->prepare(
            "INSERT INTO transactions (description, amount, category, type, date, user_id) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['description'],
            $data['amount'],
            $data['category'],
            $data['type'],
            $data['date'],
            $user_id
        ]);
        return ['message' => 'Transaction added successfully', 'id' => $this->db->getPDO()->lastInsertId()];
    }

    public function update($user_id, $id, $data)
    {
        $fields = [];
        $params = [];

        if (isset($data['description'])) {
            $fields[] = 'description = ?';
            $params[] = $data['description'];
        }
        if (isset($data['amount'])) {
            $fields[] = 'amount = ?';
            $params[] = $data['amount'];
        }
        if (isset($data['category'])) {
            $fields[] = 'category = ?';
            $params[] = $data['category'];
        }
        if (isset($data['type'])) {
            $fields[] = 'type = ?';
            $params[] = $data['type'];
        }
        if (isset($data['date'])) {
            $fields[] = 'date = ?';
            $params[] = $data['date'];
        }

        if (empty($fields)) {
            return ['error' => 'No fields to update'];
        }

        $params[] = $id;
        $params[] = $user_id;
        $sql = "UPDATE transactions SET " . implode(', ', $fields) . " WHERE id = ? AND user_id = ?";
        $stmt = $this->db->getPDO()->prepare($sql);
        $stmt->execute($params);

        return ['message' => 'Transaction updated successfully'];
    }

    public function destroy($user_id, $id)
    {
        $stmt = $this->db->getPDO()->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        return ['message' => 'Transaction deleted successfully'];
    }
}