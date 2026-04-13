<?php

declare(strict_types=1);

namespace Q23\MfaEmail\Service;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Sends MFA verification codes via email using TYPO3's mail API.
 */
class MailService
{
    /**
     * Send a verification code to the user's email address.
     */
    public function sendCode(string $email, string $name, string $code, int $validMinutes): bool
    {
        if ($email === '') {
            return false;
        }

        $extConf = $this->getExtConf();
        $siteName = trim((string)($extConf['siteName'] ?? 'My Website')) ?: 'My Website';
        $prefix = trim((string)($extConf['emailSubjectPrefix'] ?? 'Login')) ?: 'Login';
        $signature = trim((string)($extConf['emailSignature'] ?? ''));

        $subject = $prefix . ': Your verification code';
        $signatureLine = $signature !== '' ? "\n\n" . $signature : '';

        $textBody = sprintf(
            "Hello%s,\n\n"
            . "Your verification code for %s:\n\n"
            . "    %s\n\n"
            . "This code is valid for %d minutes.\n\n"
            . "If you did not initiate this login, please ignore this email "
            . "and consider changing your password as a precaution."
            . "%s",
            $name !== '' ? ' ' . $name : '',
            $siteName,
            $code,
            $validMinutes,
            $signatureLine
        );

        $signatureHtml = $signature !== ''
            ? '<p style="color: #888; font-size: 12px; margin-top: 20px;">' . htmlspecialchars($signature) . '</p>'
            : '';

        $htmlBody = sprintf(
            '<!DOCTYPE html>'
            . '<html><head><meta charset="utf-8"></head>'
            . '<body style="font-family: Arial, Helvetica, sans-serif; color: #333; '
            . 'max-width: 500px; margin: 0 auto; padding: 20px;">'
            . '<div style="border-bottom: 3px solid #003366; padding-bottom: 15px; margin-bottom: 20px;">'
            . '<strong style="color: #003366; font-size: 18px;">%s</strong>'
            . '</div>'
            . '<p>Hello%s,</p>'
            . '<p>Your verification code:</p>'
            . '<div style="font-size: 36px; font-weight: bold; letter-spacing: 10px; '
            . 'text-align: center; padding: 25px; background: #f0f4f8; '
            . 'border: 2px solid #003366; border-radius: 8px; margin: 25px 0; '
            . 'color: #003366;">%s</div>'
            . '<p>This code is valid for <strong>%d minutes</strong>.</p>'
            . '<hr style="border: none; border-top: 1px solid #ddd; margin: 25px 0;">'
            . '<p style="color: #888; font-size: 12px;">If you did not initiate this login, '
            . 'please ignore this email and consider changing your password as a precaution.</p>'
            . '%s'
            . '</body></html>',
            htmlspecialchars($siteName),
            $name !== '' ? ' ' . htmlspecialchars($name) : '',
            htmlspecialchars($code),
            $validMinutes,
            $signatureHtml
        );

        try {
            $mail = GeneralUtility::makeInstance(MailMessage::class);
            $mail
                ->to(new Address($email, $name))
                ->subject($subject)
                ->text($textBody)
                ->html($htmlBody)
                ->send();
            return true;
        } catch (\Throwable $e) {
            GeneralUtility::makeInstance(LogManager::class)
                ->getLogger(self::class)
                ->error('Failed to send MFA verification email', [
                    'exception' => $e->getMessage(),
                    'email' => $email,
                ]);
            return false;
        }
    }

    private function getExtConf(): array
    {
        try {
            return GeneralUtility::makeInstance(ExtensionConfiguration::class)
                ->get('mfa_email') ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
