<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class WhatsappController extends Controller
{
    public function sendMessage(Request $request)
    {
        $recipients = $request->get('destinatarios');
        $messageText = $request->get('mensaje');
    
        // Si el array de destinatarios está vacío, devuelve un mensaje de error.
        if (empty($recipients)) {
            return response()->json(['error' => 'Error, no se seleccionaron destinatarios'], 400);
        }
    
        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_TOKEN');
        $twilio = new Client($sid, $token);
    
        $responseMessages = [];
    
        foreach ($recipients as $phoneNumber) {
            $message = $twilio->messages
                ->create("whatsapp:+591".$phoneNumber, // agregar el código de país a cada número de teléfono
                    array(
                        "from" => "whatsapp:+14155238886", // debes cambiar esto al número de teléfono de tu aplicación en Twilio
                        "body" => $messageText
                    )
                );
    
            $responseMessages[] = ['number' => $phoneNumber, 'sid' => $message->sid];
        }
    
        return response()->json(['message' => 'Mensajes enviados con éxito!', 'messages' => $responseMessages]);
    }
    

}
