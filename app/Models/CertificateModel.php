<?php
/**
 * CertificateModel — Student certificates with verification codes.
 */

namespace App\Models;

class CertificateModel extends BaseModel
{
    protected string $table = 'certificates';
    protected string $primaryKey = 'id';
    protected array $fillable = ['student_id', 'title', 'issuing_organization', 'certificate_number', 'issued_date', 'expiry_date', 'verification_code', 'verified'];

    public function getForStudent(int $studentId): array
    {
        return $this->query("SELECT * FROM certificates WHERE student_id = ? ORDER BY issued_date DESC", [$studentId]);
    }

    public function findByVerificationCode(string $code): ?array
    {
        return $this->queryOne("
            SELECT c.*, u.first_name, u.last_name, u.email, s.department
            FROM certificates c
            JOIN students s ON s.id = c.student_id
            JOIN users u ON u.id = s.user_id
            WHERE c.verification_code = ?
        ", [$code]);
    }

    public function generateVerificationCode(): string
    {
        return 'SS-' . strtoupper(substr(bin2hex(random_bytes(8)), 0, 12));
    }
}
