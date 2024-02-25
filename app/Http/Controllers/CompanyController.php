<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CompanyRepository;
use App\Repositories\ElasticSearchRepository;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\CompanyNameTransformer;

class CompanyController extends Controller
{
    private $companyRepository;
    private $elasticSearchRepository;

    public function __construct(CompanyRepository $companyRepository, ElasticSearchRepository $elasticSearchRepository)
    {
        $this->companyRepository = $companyRepository;
        $this->elasticSearchRepository = $elasticSearchRepository;
    }

    private function validateBiin(Request $request)
    {
        return Validator::make($request->all(), [
            'biin' => ['numeric', 'digits:12'],
        ], [
            'biin.numeric' => 'BIN должен содержать только цифры',
            'biin.digits' => 'BIN должен содержать 12 цифр',
        ]);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string',
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $keyword = $request->input('keyword');
        $page = $request->input('page', 1); 
        $limit = $request->input('limit', 10); 

        $searchResults = $this->elasticSearchRepository->searchAll($keyword, $page, $limit);

        // Преобразовываем имена компаний
        if(isset($searchResults['data'])) {
            foreach($searchResults['data'] as &$result) {
                $result['name'] = CompanyNameTransformer::transform($result['name']);
            }
        }
        
        return response()->json($searchResults, Response::HTTP_OK);
    }


    public function findCompanyByBin(Request $request, $biin)
    {
        $validator = $this->validateBiin($request);
        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $company = $this->companyRepository->getByBiin($biin);
            if (!$company) {
                return response()->json(['message' => 'Компания не найдена'], Response::HTTP_NOT_FOUND);
            }
            return response()->json($company, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findCompanyByOked(Request $request, $biin)
    {
        $validator = $this->validateBiin($request);
        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $company = $this->companyRepository->getByOked($biin);
            if (!$company) {
                return response()->json(['message' => 'Ближайшие конкуренты не найдены'], Response::HTTP_NOT_FOUND);
            }
            return response()->json($company, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findCompanyByOkedRegion(Request $request, $biin)
    {
        $validator = $this->validateBiin($request);
        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $company = $this->companyRepository->getByOkedRegion($biin);
            if (!$company) {
                return response()->json(['message' => 'Конкуренты в регионе не найдены'], Response::HTTP_NOT_FOUND);
            }
            return response()->json($company, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function findCompanyByAddress(Request $request, $biin)
    {
        $validator = $this->validateBiin($request);
        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $company = $this->companyRepository->getBySimilarAddress($biin);
            if (!$company) {
                return response()->json(['message' => 'Компании с похожим адресом не найдены'], Response::HTTP_NOT_FOUND);
            }
            return response()->json($company, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
