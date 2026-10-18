<?php

namespace App\Services\Mail;

use App\Models\OrgSmtpSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TenantMailer
{
    /**
     * Konfigurasi mailer dinamik berdasarkan tetapan SMTP organisasi.
     */
    public static function configureForOrg(?OrgSmtpSetting $smtp): bool
    {
        if (!$smtp || !$smtp->is_active || empty($smtp->mail_host)) {
            return false;
        }

        $encryption = $smtp->mail_encryption;
        if ($encryption === 'none' || empty($encryption)) {
            $encryption = null;
        }

        Config::set('mail.mailers.tenant_smtp', [
            'transport' => 'smtp',
            'host' => $smtp->mail_host,
            'port' => (int) ($smtp->mail_port ?: 587),
            'encryption' => $encryption,
            'username' => $smtp->mail_username,
            'password' => $smtp->mail_password,
            'timeout' => 10,
        ]);

        if (!empty($smtp->mail_from_address)) {
            Config::set('mail.from.address', $smtp->mail_from_address);
        }
        if (!empty($smtp->mail_from_name)) {
            Config::set('mail.from.name', $smtp->mail_from_name);
        }

        Mail::purge('tenant_smtp');

        return true;
    }

    /**
     * Dapatkan instance mailer (tenant_smtp sekiranya aktif, atau mailer lalai).
     */
    public static function getMailer(?OrgSmtpSetting $smtp)
    {
        if (static::configureForOrg($smtp)) {
            return Mail::mailer('tenant_smtp');
        }

        return Mail::mailer();
    }

    /**
     * Hantar emel ujian untuk mengesahkan konfigurasi SMTP organisasi.
     */
    public static function sendTestEmail(OrgSmtpSetting $smtp, string $recipientEmail, string $orgName, bool $isMalay = true): array
    {
        if (empty($smtp->mail_host)) {
            return [
                'success' => false,
                'message' => $isMalay
                    ? 'Hos pelayan SMTP (Mail Host) belum ditetapkan.'
                    : 'SMTP Mail Host has not been configured.',
            ];
        }

        $encryption = $smtp->mail_encryption;
        if ($encryption === 'none' || empty($encryption)) {
            $encryption = null;
        }

        Config::set('mail.mailers.tenant_smtp_test', [
            'transport' => 'smtp',
            'host' => $smtp->mail_host,
            'port' => (int) ($smtp->mail_port ?: 587),
            'encryption' => $encryption,
            'username' => $smtp->mail_username,
            'password' => $smtp->mail_password,
            'timeout' => 10,
        ]);

        Mail::purge('tenant_smtp_test');

        try {
            $fromAddress = $smtp->mail_from_address ?: config('mail.from.address') ?: 'no-reply@kerjaai.my';
            $fromName = $smtp->mail_from_name ?: $orgName ?: config('mail.from.name') ?: 'KerjaAI';

            $subject = $isMalay
                ? "Emel Ujian SMTP — {$orgName}"
                : "SMTP Test Email — {$orgName}";

            $body = $isMalay
                ? "Salam sejahtera,\n\nIni adalah emel ujian daripada KerjaAI bagi mengesahkan konfigurasi pelayan SMTP organisasi anda ({$orgName}) telah berjaya disambungkan dan berfungsi dengan selamat.\n\nMaklumat Sambungan:\n• Hos: {$smtp->mail_host}\n• Port: {$smtp->mail_port}\n• Penyulitan: " . strtoupper($smtp->mail_encryption ?: 'Tiada') . "\n• Emel Penghantar: {$fromAddress}\n\nSekian, terima kasih.\nSistem Operasi KerjaAI."
                : "Greetings,\n\nThis is a test email from KerjaAI confirming that your organization ({$orgName}) SMTP gateway is successfully connected and operational.\n\nConnection Details:\n• Host: {$smtp->mail_host}\n• Port: {$smtp->mail_port}\n• Encryption: " . strtoupper($smtp->mail_encryption ?: 'None') . "\n• From: {$fromAddress}\n\nBest regards,\nKerjaAI Operating System.";

            Mail::mailer('tenant_smtp_test')->raw($body, function ($message) use ($recipientEmail, $fromAddress, $fromName, $subject) {
                $message->to($recipientEmail)
                    ->from($fromAddress, $fromName)
                    ->subject($subject);
            });

            return [
                'success' => true,
                'message' => $isMalay
                    ? "Emel ujian berjaya dihantar ke {$recipientEmail}! Sambungan SMTP disahkan sah dan selamat."
                    : "Test email successfully sent to {$recipientEmail}! SMTP connection verified and secure.",
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => ($isMalay ? 'Gagal menyambung ke pelayan SMTP: ' : 'Failed to connect to SMTP server: ') . $e->getMessage(),
            ];
        } finally {
            Mail::purge('tenant_smtp_test');
        }
    }
}
