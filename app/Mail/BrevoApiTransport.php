<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class BrevoApiTransport extends AbstractTransport
{
	public function __construct(protected string $apiKey)
	{
		parent::__construct();
	}

	protected function doSend(SentMessage $message): void
	{
		$email = MessageConverter::toEmail($message->getOriginalMessage());

		$payload = array_filter([
			'subject' => $email->getSubject(),
			'sender' => $this->formatAddress($email->getFrom()[0] ?? null),
			'to' => $this->formatAddresses($email->getTo()),
			'cc' => $this->formatAddresses($email->getCc()) ?: null,
			'bcc' => $this->formatAddresses($email->getBcc()) ?: null,
			'replyTo' => $this->formatAddress($email->getReplyTo()[0] ?? null),
			'htmlContent' => $email->getHtmlBody() ?: null,
			'textContent' => $email->getTextBody() ?: null,
		], fn($v) => $v !== null && $v !== []);

		$response = Http::withHeaders([
			'api-key' => $this->apiKey,
			'accept' => 'application/json',
		])->post('https://api.brevo.com/v3/smtp/email', $payload);

		if ($response->failed()) {
			throw new RuntimeException('Brevo API error: ' . $response->body());
		}
	}

	protected function formatAddress(?Address $address): ?array
	{
		if (! $address) {
			return null;
		}

		return array_filter([
			'email' => $address->getAddress(),
			'name' => $address->getName() ?: null,
		]);
	}

	protected function formatAddresses(array $addresses): array
	{
		return array_values(array_filter(array_map(fn($a) => $this->formatAddress($a), $addresses)));
	}

	public function __toString(): string
	{
		return 'brevo+api://';
	}
}
