<?php

namespace App\Http\Controllers;

use App\Service\CountryAndTimzoneService;
use App\Models\PhoneBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PhoneBookController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $bookPhones= auth()->user()->bookPhones;
        $ids = $bookPhones->pluck('id')->toArray();;
        $data = PhoneBook::whereIn('id',$ids)
            ->orderBy('id', 'asc')
            ->paginate(3)
            ->shuffle()
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = auth()->user()->id;
        $bookPhone = auth()->user()->bookPhones()->find($id);

        if (!$bookPhone) {
            $text = 'Book phone not found';
            Log::error( $text, ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' => $text
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $bookPhone->toArray()
        ], 400);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $user = auth()->user()->id;
        $text = null;
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'nullable',
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'country_code' => 'nullable',
            'timezone_name' => 'nullable',
        ]);

        if ($validator->fails()) {
            $responseArr['message'] = $validator->errors();
            Log::error( $responseArr, ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' => $responseArr
            ], 400);
        }

        $timzone = CountryAndTimzoneService::getTimzone();
        $continents = CountryAndTimzoneService::getContinents();
        if(!in_array( $request->timezone_name, $timzone )){
            $text = 'Timzone not found';
            Log::error( $text, ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' =>  $text
            ], 400);
        }

        if(!in_array( $request->country_code, $continents)){
            $text = 'Country code not found';
            Log::error( $text, ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' => $text
            ], 400);
        }


        $bookPhone = new PhoneBook();
        $bookPhone->first_name = $request->first_name;
        $bookPhone->last_name = $request->last_name;
        $bookPhone->phone_number = $request->phone_number;
        $bookPhone->country_code = $request->country_code;
        $bookPhone->timezone_name = $request->timezone_name;

        if (auth()->user()->bookPhones()->save($bookPhone)){
            return response()->json([
                'success' => true,
                'data' => $bookPhone->toArray()
            ]);
        }else{
            $text = 'Book phone not added';
            Log::error( $text, ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' => $text
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user= auth()->user()->id;
        $text = null;
        $bookPhone = auth()->user()->bookPhones()->find($id);

        if (!$bookPhone) {
            $text = 'Book phone not found for update';
            Log::error( $text, ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' => $text
            ], 400);
        }

        $updated = $bookPhone->fill($request->all())->save();

        if ($updated){
            return response()->json([
                'success' => true
            ]);
        }else{
            $text = 'Book phone can not be updated';
            Log::error( $text, ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' => $text
            ], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $user= auth()->user()->id;
        $text = null;
        $bookPhone = auth()->user()->bookPhones()->find($id);

        if (!$bookPhone) {
            $text = 'Book phone not found for delete';
            Log::error( $text, ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' => $text
            ], 400);
        }

        if ($bookPhone->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            $text = 'Book phone can not be deleted';
            Log::error( $text, ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' =>  $text
            ], 500);
        }
    }
}
