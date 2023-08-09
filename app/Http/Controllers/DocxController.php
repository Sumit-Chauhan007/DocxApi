<?php

namespace App\Http\Controllers;

use App\Models\Docx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DocxController extends Controller
{
    public function index()
    {
        $docx = Docx::get();
        $id = 0;
        return view('home', compact('docx', 'id'));
    }
    public function AskAndUpload(Request $request)
    {
        $file = $request->file('document');
        // $filesize = $file->getSize();
        if ($file != '') {
            $apiKey = env('ASK_MY_PDF_API');

            $apiUrl = 'https://api.askyourpdf.com/v1/api/upload';
            $filename = $_FILES['document']['name'];
            $filedata = $_FILES['document']['tmp_name'];
            $filesize = $_FILES['document']['size'];
            $headers = [
                'x-api-key: ' . $apiKey,
                'Content-Type: multipart/form-data',
            ];
            $postfields = [
                'file' => curl_file_create($filedata, $file->getClientMimeType(), $filename),
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            $response = json_decode(curl_exec($ch));
            // dd($response);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                return response()->json(['error' => $err]);
            }
            $data = Docx::where('document_Id', $response->docId)->first();
            if ($data) {
                return response()->json(['error' => 'Already Added with document Id::' . $response->docId . '']);
            }
            $data = new Docx();
            $data->uuid = Str::uuid();
            $data->document_Id = $response->docId;
            $data->save();
            $id = $response->docId;
            $openaiApiKey = env('OPEN_AI_API_KEY');

            $headers = [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
            ];

            $data = [
                [
                    "sender" => "User",
                    "message" => "Read this PDF and tell me what are my possible Tax Deductions.If there is no specific information regarding tax deductions then send me nothing and dont write 'in json format' in the response",
                ],
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.askyourpdf.com/v1/chat/{$id}");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($httpCode === 200) {

                $responseData = json_decode($response, true);
                $answerMessage = $responseData['answer']['message'];

                if (preg_match('/\d+/', $answerMessage)) {
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $openaiApiKey,
                    ])->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4',
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => "{$answerMessage} .Read this financial data and If there is no data or deductions then send me an empty array or else tell me what are my possible Tax Deductions in json format.   ",
                            ],
                        ],
                    ]);

                    $ChatGptAnswer = $response->json();
                    // dd($ChatGptAnswer);
                    $ChatresponseData = $ChatGptAnswer['choices'][0]['message']['content'];
                    return response()->json([
                        'message' => $ChatresponseData
                    ]);
                } else {
                    return response()->json([], 204);
                }
            } else {
                $errorStatus = $httpCode;
                return response()->json(['html' => $errorStatus]);
            }
        } else {
            $errmsg = "No file uploaded.";
            return response()->json(['error' => $errmsg]);
        }
    }

    function delete(Request $request)
    {
        $id = $request->id;
        $apiKey = env('ASK_MY_PDF_API');
        $headers = [
            'x-api-key: ' . $apiKey,
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.askyourpdf.com/v1/api/documents/' . $id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status === 200) {
            Docx::where('document_Id', $id)->delete();
            $docx = Docx::get();
            $id = 0;
            $html = '';
            foreach ($docx as $doc) {
                $html .= '<tr>
            <td>' . $id . '</td>
            <td>' . $doc->document_Id . '</td>
            <td>' . $doc->created_at . '</td>
            <td><button onclick=ask("' . $doc->document_Id . '") class="btn btn-primary">Get Detail</button></td>
            <td><button onclick=delet("' . $doc->document_Id . '") class="btn btn-primary">Delete</button></td>
            </tr>';
                $id++;
            }
            return response()->json(['html' => $html]);
        } else {
            $errorStatus = $status;
            return response()->json(['error' => 'Error: ' . $errorStatus]);
        }
        curl_close($ch);
    }
}
