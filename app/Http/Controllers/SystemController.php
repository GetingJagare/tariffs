<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Regions;
use App\Services\TariffsExporter;
use App\Services\TariffsImporter;
use App\TariffFieldTypes;
use App\TariffFieldValues;
use App\Tariffs;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class SystemController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('system', ['user' => Auth::user()]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getTariffs(Request $request)
    {
        $id = $request->get('id');

        if ($id){

            /** @var Tariffs $tariff */
            $tariff = Tariffs::where(['id' => $id])->with(['region', 'category', 'fieldValues.field.type'])->first();

            foreach ($tariff->fieldValues as &$fieldValue) {

                switch($fieldValue->field->type->alias) {

                    case TariffFieldTypes::TYPE_CHECKBOX:

                        $fieldValue->value = (bool)$fieldValue->value;

                        break;

                }

            }

            return ['tariff' => $tariff->toArray()];

        }

        $skip = $request->get('skip');
        $count = $request->get('count');

        $tariffs = Tariffs::query()->with(['region', 'category', 'fieldValues.field']);
        $tariffsCount = $tariffs->count();

        if ($skip) {
            $tariffs->skip($skip);
        }

        if ($count) {
            $tariffs->limit($count);
        }

        return ['tariffs' => $tariffs->get(), 'count' => $tariffsCount];
    }

    /**
     * @param Request $request
     * @param TariffsImporter $tariffsImporter
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws Exception
     */
    public function importTariffs(Request $request, TariffsImporter $tariffsImporter)
    {
        $tariffsImporter->checkFile();

        if (($errors = $tariffsImporter->getErrors())) {

            return ['status' => 0, 'error' => join(PHP_EOL, $errors)];

        }

        $tariffsImporter->doImport();


        return ['status' => 1];
    }

    /**
     * @param Request $request
     * @param TariffsExporter $tariffsExporter
     * @return array
     */
    public function exportTariffs(Request $request, TariffsExporter $tariffsExporter)
    {
        $tariffsExporter->doExport();

        return ['link' => env('APP_URL') . "/yml/{$tariffsExporter->getYmlFilename()}"];
    }

    /**
     * @return array
     */
    public function checkFeed() {

        $ymlFilePath = "/yml/tariffs_export.xml";

        if (file_exists(public_path() . $ymlFilePath)) {

            return ['link' => env('APP_URL') . $ymlFilePath];

        }

        return ['link' => null];

    }

    /**
     * @param Request $request
     * @throws \Exception
     * @return array
     */
    public function deleteTariff(Request $request)
    {

        $tariffId = $request->get('id');

        if ($tariffId) {

            /** @var Tariffs $tariff */
            $tariff = Tariffs::where(['id' => $tariffId])->first();

            if ($tariff) {
                $tariff->delete();

                return ['status' => 1];
            } else {

                return ['status' => 0, 'error' => "Тариф с id = $tariffId не найден"];

            }

        }

        return ['status' => 0, 'error' => "Не задан id тарифа"];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function saveTariff(Request $request)
    {

        $tariff = $request->get('tariff');

        $tariffEntity = $tariff['id'] ? Tariffs::where(['id' => $tariff['id']])->first() : new Tariffs();

        $category = Categories::where(['name' => $tariff['category']['name']])->first();

        if (!$category) {

            $category = new Categories(['name' => $tariff['category']['name']]);
            $category->save();

        }

        $tariffEntity->category_id = $category->id;

        $region = Regions::where(['name' => $tariff['region']['name']])->first();

        if (!$region) {

            $region = new Regions(['name' => $tariff['region']['name']]);
            $region->save();

        }

        $tariffEntity->region_id = $region->id;

        $tariffEntity->fill($tariff);
        $tariffEntity->save();

        foreach ($tariff['field_values'] as &$fieldValue) {

            $fieldValueEntity = !isset($fieldValue['id']) ? new TariffFieldValues()
                : TariffFieldValues::where(['id' => $fieldValue['id']])->first();

            switch($fieldValue['field']['type']['alias']) {

                case TariffFieldTypes::TYPE_CHECKBOX:

                    $fieldValue['value'] = (int)$fieldValue['value'];

                    break;

            }

            $fieldValue['tariffs_id'] = $tariffEntity->id;

            $fieldValueEntity->fill($fieldValue);
            $fieldValueEntity->save();

        }

        return ['status' => 1, 'id' => $tariffEntity->id];

    }
}
