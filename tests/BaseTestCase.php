<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

abstract class BaseTestCase extends ApiTestCase
{
    /**
     * Session ID for session-based authentication (2FA approach).
     */
    public static ?string $mySessionId = null;

    /**
     * JWT token for JWT-based authentication.
     * If using JWT auth, override logIn() to set this instead of $mySessionId.
     */
    public static ?string $myJwt = null;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->loadFixtures();
    }

    /**
     * Get the Entity Manager.
     */
    protected function getEm(): EntityManagerInterface
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        Assert::isInstanceOf($em, EntityManagerInterface::class);

        return $em;
    }

    /**
     * Get a service from the container.
     *
     * @template T of object
     *
     * @param class-string<T> $classString
     *
     * @return T
     */
    protected function getService(string $classString): object
    {
        $service = static::getContainer()->get($classString);
        Assert::isInstanceOf($service, $classString);

        // @phpstan-ignore-next-line
        return $service;
    }

    /**
     * Load fixtures by running the reset-test-fixtures command.
     */
    private function loadFixtures(): void
    {
        Assert::notNull(self::$kernel);
        $env = self::$kernel->getEnvironment();
        $process = new Process(
            ['composer', 'reset-test-fixtures', '--', "--env={$env}"],
            self::$kernel->getProjectDir()
        );
        $process->mustRun();
    }

    /**
     * Perform an API request with automatically managed authentication headers.
     *
     * Supports both session-based (2FA) and JWT authentication.
     *
     * @param array<string, string> $headers
     */
    public static function request(string $method, string $url, mixed $json = null, array $headers = []): ResponseInterface
    {
        // Handle JWT authentication
        if (static::$myJwt) {
            $headers['Authorization'] = 'Bearer '.static::$myJwt;
        }
        // Handle session-based authentication (2FA)
        elseif (static::$mySessionId) {
            $headers['Authorization'] = static::$mySessionId;
        }

        // Set appropriate Content-Type
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'PATCH' === $method
                ? 'application/merge-patch+json'
                : 'application/ld+json';
        }

        $response = static::createClient()->request(
            $method,
            $url,
            [
                'headers' => $headers,
                'json' => $json ?? [],
            ]
        );

        $response->getHeaders(throw: false);

        return $response;
    }

    protected function logIn(?string $email = 'tim@example.com', ?string $password = 'Test123!'): void
    {
        $response = static::createClient()
            ->request('POST', '/login-check', [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
            ]);
        $token = $response->toArray()['token'];
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        static::$myJwt = $token;
    }

    /**
     * Log out the current user.
     */
    public function logOut(): void
    {
        static::$mySessionId = null;
        static::$myJwt = null;
    }

    /**
     * Get a command tester for testing console commands.
     */
    protected function getCommandTester(string $command): CommandTester
    {
        self::bootKernel();
        Assert::notNull($kernel = self::$kernel);
        $application = new Application($kernel);

        $command = $application->find($command);

        return new CommandTester($command);
    }

    /**
     * Extract UUID from an IRI string.
     * Example: "/api/users/123e4567-e89b-12d3-a456-426614174000" -> "123e4567-e89b-12d3-a456-426614174000"
     */
    protected function extractUuid(mixed $id): string
    {
        Assert::string($id);
        $parts = explode('/', trim($id, '/'));

        return end($parts);
    }
}
