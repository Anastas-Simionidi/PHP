<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;
use RuntimeException;
use Throwable;

final readonly class BookRepository
{
 public function __construct(private PDO $pdo) {}

 public function create(array $data): int
 {
 $stmt = $this->pdo->prepare(
 'INSERT INTO books (isbn, title, author, copies) VALUES (:isbn, :title, :author, :copies)'
 );
 $stmt->execute([
 'isbn' => $data['isbn'],
 'title' => $data['title'],
 'author' => $data['author'],
 'copies' => (int)$data['copies']
 ]);
 return (int)$this->pdo->lastInsertId();
 }

 public function find(int $id): ?array
 {
 $stmt = $this->pdo->prepare(
 'SELECT id, isbn, title, author, copies, created_at, updated_at FROM books WHERE id = :id'
 );
 $stmt->execute(['id' => $id]);
 $row = $stmt->fetch();
 return $row === false ? null : $row;
 }

public function findAll(string $search = '', string $sort = 'title', int $limit = 20, int $offset = 0): array
 {
 $allowedSort = ['title', 'isbn', 'author', 'copies', 'created_at'];
 if (!in_array($sort, $allowedSort, true)) {
 $sort = 'title';
 }

 // Используем уникальные плейсхолдеры :search1, :search2, :search3
 $sql = "SELECT id, isbn, title, author, copies FROM books 
 WHERE title LIKE :search1 OR isbn LIKE :search2 OR author LIKE :search3
 ORDER BY $sort LIMIT :limit OFFSET :offset";

 $stmt = $this->pdo->prepare($sql);
 
 $searchTerm = '%' . $search . '%';
 $stmt->bindValue('search1', $searchTerm, PDO::PARAM_STR);
 $stmt->bindValue('search2', $searchTerm, PDO::PARAM_STR);
 $stmt->bindValue('search3', $searchTerm, PDO::PARAM_STR);
 
 $stmt->bindValue('limit', max(1, min($limit, 100)), PDO::PARAM_INT);
 $stmt->bindValue('offset', max(0, $offset), PDO::PARAM_INT);
 $stmt->execute();

 return $stmt->fetchAll();
 }


 public function delete(int $id): bool
 {
 $stmt = $this->pdo->prepare('DELETE FROM books WHERE id = :id');
 $stmt->execute(['id' => $id]);
 return $stmt->rowCount() === 1;
 }

 /**
 * Составная транзакционная операция: уменьшение количества копий и запись в аудит.
 */
 public function issueBookWithAudit(int $id): void
 {
 try {
 $this->pdo->beginTransaction();

 // Шаг 1: Списание одной копии книги, если они есть в наличии
 $update = $this->pdo->prepare(
 'UPDATE books SET copies = copies - 1 WHERE id = :id AND copies > 0'
 );
 $update->execute(['id' => $id]);

 if ($update->rowCount() !== 1) {
 throw new RuntimeException('Книги нет в наличии или она не найдена');
 }

 // Шаг 2: Логирование в аудит
 $log = $this->pdo->prepare(
 'INSERT INTO audit_log (entity_type, entity_id, action) VALUES (:type, :id, :action)'
 );
 $log->execute([
 'type' => 'book',
 'id' => $id,
 'action' => 'issued'
 ]);

 $this->pdo->commit();
 } catch (Throwable $error) {
 if ($this->pdo->inTransaction()) {
 $this->pdo->rollBack();
 }
 throw $error;
 }
 }
}
