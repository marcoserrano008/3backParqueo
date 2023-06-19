<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class WhatsappController extends Controller
{
    public function sendMessage(Request $request)
    {
        // valida los datos de entrada
        $request->validate([
            'mensaje' => 'required',
            'destinatarios' => 'required|array',
        ]);

        // inicializa el cliente de guzzle
        $client = new Client();

        foreach ($request->destinatarios as $destinatario) {
            $url = 'https://graph.facebook.com/v17.0/109661425497288/messages'; 

            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer EAAKPOV7RBqwBAJrvoYf65YQwfCbwcDIcaDhxT6di4pttYevCtoi7C0AqZCx35ubaxKXp8fseNr1XInt5P43D5t6xBRq0E59s8ZAejLVf0ZCifqCAvX3Kv22f05S4LLGKUQgtjx6QeQt0d9GLZAGr6OTlncO4A2VqUEYrT364LethoTVBixZA2elHEWhaDUvdhZAptDd6JTu8wnYgh5VcZBH'
                ],
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => $destinatario,
                    'type' => 'template',
                    'template' => [
                        'name' => 'hello_world',
                        'language' => [
                            'code' => 'en_US',
                        ],
                    ],
                ],
            ]);

            // Puedes agregar manejo de errores para verificar que la solicitud fue exitosa
        }

        return response()->json(['message' => 'Mensajes enviados exitosamente']);
    }
}
