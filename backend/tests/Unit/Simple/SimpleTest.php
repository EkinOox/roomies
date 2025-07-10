<?php

namespace App\Tests\Unit\Simple;

use PHPUnit\Framework\TestCase;

/**
 * Test simple pour vérifier que PHPUnit fonctionne
 * Ce test ne dépend d'aucun service Symfony
 */
class SimpleTest extends TestCase
{
    /**
     * Test de base pour vérifier que PHPUnit est configuré
     */
    public function testBasicAssertion(): void
    {
        // Test simple d'addition
        $result = 2 + 2;
        $this->assertEquals(4, $result);
        
        // Test de chaéne
        $text = 'Hello Roomies';
        $this->assertStringContainsString('Roomies', $text);
        
        // Test de tableau
        $array = ['a', 'b', 'c'];
        $this->assertCount(3, $array);
        $this->assertContains('b', $array);
    }

    /**
     * Test de logique métier simple
     */
    public function testStringManipulation(): void
    {
        $email = 'TEST@EXAMPLE.COM';
        $normalized = strtolower($email);
        
        $this->assertEquals('test@example.com', $normalized);
        $this->assertTrue(filter_var($normalized, FILTER_VALIDATE_EMAIL) !== false);
    }

    /**
     * Test avec données fournies par un data provider
     * @dataProvider validEmailProvider
     */
    public function testEmailValidation(string $email, bool $expected): void
    {
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $this->assertEquals($expected, $isValid);
    }

    /**
     * Data provider pour les tests d'email
     */
    public function validEmailProvider(): array
    {
        return [
            ['test@example.com', true],
            ['user.name@domain.co.uk', true],
            ['invalid.email', false],
            ['@domain.com', false],
            ['user@', false],
            ['', false],
        ];
    }

    /**
     * Test d'exception
     */
    public function testExceptionHandling(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero');
        
        $this->divideNumbers(10, 0);
    }

    /**
     * Méthode utilitaire pour le test d'exception
     */
    private function divideNumbers(int $a, int $b): float
    {
        if ($b === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        
        return $a / $b;
    }
}