<?php

namespace App\Domain\Email;

use Generator;

class EmailRepository
{
    /**
     * @var string
     */
    private string $file;

    public function __construct()
    {
        $this->file = __DIR__ . '/../../../var/storage/emails.txt';
    }

    /**
     * Записати email у файл
     * @param $email
     * @return true
     * @throws EmailExistException
     */
    public function storeEmail($email): bool
    {
        // Перевірити, чи існує вже email у файловій базі даних
        if (!$this->isExistEmail($email)) {
            file_put_contents($this->file, $email . PHP_EOL, FILE_APPEND);
            return true;
        }

        throw new EmailExistException();
    }

    /**
     * Отримати всі електронні адреси з файлової бази даних
     */
    public function findAll(): Generator
    {
        if (is_file($this->file)) {
            $handle = fopen($this->file, 'rb');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $line = trim($line);
                    if ($line) {
                        yield $line;
                    }
                }
                fclose($handle);
            }
        }
    }

    /**
     * Перевірити, чи існує email у файловій базі даних
     * @param string $email
     * @return bool
     */
    private function isExistEmail(string $email): bool
    {
        foreach ($this->findAll() as $emailInFile) {
            if ($emailInFile === $email) {
                return true;
            }
        }

        return false;
    }
}
