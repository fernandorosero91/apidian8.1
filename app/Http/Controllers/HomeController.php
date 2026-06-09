<?php

namespace App\Http\Controllers;

use App\Company;
use App\Document;
use App\DocumentPayroll;
use App\ReceivedDocument;
use App\User;
use GuzzleHttp\Client;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companies = Company::with('user')->get()->transform(function ($row) {
            $row->total_documents = Document::where('identification_number', $row->identification_number)->count();
            return $row;
        });

        $stats = [
            'companies' => $companies->count(),
            'active_companies' => $companies->where('state', '!=', 0)->count(),
            'documents' => Document::count(),
            'payrolls' => DocumentPayroll::count(),
            'users' => User::count(),
        ];

        return view('admin.dashboard', compact('companies', 'stats'));
    }

    public function toggleCompanyState($identification_number)
    {
        $company = Company::where('identification_number', $identification_number)->firstOrFail();
        $company->state = $company->state === 0 ? 1 : 0;
        $company->save();

        return back()->with('success', $company->state ? 'Empresa activada' : 'Empresa desactivada');
    }

    public function company(Company $company)
    {
        $documents = Document::where('identification_number', $company->identification_number)
            ->orderBy('id', 'DESC')
            ->paginate(20);

        return view('company.documents', ['company' => $company, 'documents' => $documents]);
    }

    public function getXml(Company $company, $cufe)
    {
        $token = $company->user->api_token;
        $url = url('/api/ubl2.1/xml/document/' . $cufe);

        $client = new Client();
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ]
        ]);

        $responseBody = json_decode($response->getBody(), true);

        if ($response->getStatusCode() == 200) {
            return response()->json($responseBody);
        } else {
            return response()->json([
                'error' => 'Error al hacer la solicitud a la API',
                'status_code' => $response->getStatusCode(),
                'body' => $responseBody,
            ], $response->getStatusCode());
        }
    }

    public function events($company_idnumber)
    {
        $documents = ReceivedDocument::where('customer', '=', $company_idnumber)
            ->where('state_document_id', '=', 1)
            ->paginate(10);
        return view('company.events', compact('documents', 'company_idnumber'));
    }

    public function payrolls($company_idnumber)
    {
        $documents = DocumentPayroll::where('state_document_id', '=', 1)
            ->where('identification_number', $company_idnumber)
            ->paginate(20);
        return view('company.payrolls', compact('documents', 'company_idnumber'));
    }
}
