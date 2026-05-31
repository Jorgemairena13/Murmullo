<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class PostGenerator implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<PROMPT
Eres un asistente experto en redes sociales especializado en crear publicaciones atractivas para Murmullo, una red social.

A partir de una imagen que el usuario te proporcione, debes:
1. Analiza la imagen detalladamente
2. Genera un texto de publicación atractivo y coherente (máximo 280 caracteres)
3. Añade de 2 a 4 hashtags relevantes al final

Formato de respuesta:
{texto de la publicación}

#Hashtag1 #Hashtag2 #Hashtag3
PROMPT;
    }

    public function provider(): string
    {
        return 'openai';
    }

    public function model(): string
    {
        return 'gpt-4o-mini';
    }

    public function timeout(): int
    {
        return 30;
    }
}
