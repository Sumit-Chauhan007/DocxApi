<?php

namespace App\Http\Controllers;

use App\Models\Docx;
use CURLFile;
use GuzzleHttp\Client;
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
    public function upload(Request $request)
    {
        $file = $request->file('document');
        // $filesize = $file->getSize();

        if ($file != '') {
            $apiKey = 'ask_ab99f4d317181aa14f625b660d6d3e55';
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
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                return response()->json(['error' => $err]);
            }
            $data = Docx::where('document_Id', $response->docId)->first();
            if ($data) {
                return response()->json(['error'=>'Already Added with document Id::'.$response->docId.'']);
            }

     
            $data = new Docx();
            $data->uuid = Str::uuid();
            $data->document_Id = $response->docId;
            $data->save();
            $docx = Docx::get();
            $id = 0;
            $html = '';
            foreach ($docx as $doc) {
                $html .= '<tr>
            <td>' . $id . '</td>
            <td>' . $doc->document_Id . '</td>
            <td>' . $doc->created_at . '</td>
            </tr>';
                $id++;
            }
            return response()->json(['html' => $html]);
        } else {
            $errmsg = "No file uploaded.";
            return response()->json(['error' => $errmsg]);
        }
        // curl_close($ch);


    }
    function ask(Request $request){
        $id = '0d07d2a0-6c49-446b-be8d-48f02aefb14b';
        $apiKey = 'ask_0e6c7879eed84cc6ad69d3678bbc6200';
        $apiUrl = 'https://api.askyourpdf.com/v1/chat/0d07d2a0-6c49-446b-be8d-48f02aefb14b';
        $data = '[
            {
              "sender": "user",
              "message": "What does this document say?"
            }
          ]'; 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'x-api-key: ' . $apiKey,
            'Content-Type' => 'application/json',
            ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = json_decode(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        return response()->json(['hmtl'=>$response]);
    }
}
