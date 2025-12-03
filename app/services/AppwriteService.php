<?php

namespace App\Services;

use Appwrite\Client;
use Appwrite\Service\Databases;
use Appwrite\ID;

class AppwriteService
{
    protected Client $client;
    public Databases $databases;

    public function __construct()
    {
        $this->client = new Client();

        // Inicializa el cliente usando las variables de entorno
        $this->client
            ->setEndpoint(env('APPWRITE_ENDPOINT'))
            ->setProject(env('APPWRITE_PROJECT_ID'))
            // **Crucial para el back-end:** Usa la clave de administrador
            ->setKey(env('APPWRITE_API_KEY'));

        // Inicializa los servicios que usarás (ej. Bases de Datos)
        $this->databases = new Databases($this->client);
    }

    /**
     * Retorna el ID de la base de datos configurado en el .env
     */
    public function getDatabaseId(): string
    {
        return env('APPWRITE_DATABASE_ID');
    }

    // Puedes agregar más funciones de ayuda aquí, como getCollectionId('posts')
}
