<?php

// tests/Form/LoginFormTest.php



use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginFormTest extends WebTestCase
{
    public function testLoginFormCsrfToken(): void
    {
        // Tworzymy klienta HTTP
        $client = static::createClient();

        // Wywołujemy stronę logowania
        $crawler = $client->request('GET', '/login');

        // Sprawdzamy, czy formularz logowania jest dostępny
        $this->assertResponseIsSuccessful();

        // Pobieramy token CSRF z formularza
        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');

        // Sprawdzamy, czy token CSRF istnieje
        $this->assertNotEmpty($csrfToken);

        // Przygotowujemy dane logowania z poprawnym tokenem CSRF
        $formData = [
            'login_form[username]' => 'testuser', // Przykładowa nazwa użytkownika
            'login_form[password]' => 'testpassword', // Przykładowe hasło
            'login_form[_csrf_token]' => $csrfToken, // Token CSRF
        ];

        // Wysyłamy dane formularza logowania
        $client->request('POST', '/login', $formData);

        // Sprawdzamy, czy logowanie powiodło się (np. przekierowanie po pomyślnym logowaniu)
        $this->assertResponseRedirects('/dashboard'); // Zmienna na stronę po pomyślnym logowaniu
    }
}


