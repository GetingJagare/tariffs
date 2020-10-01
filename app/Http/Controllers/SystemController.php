<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Regions;
use App\Services\TariffsExporter;
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
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws Exception
     */
    public function importTariffs(Request $request)
    {

        /** @var UploadedFile $file */
        if (!($file = $request->file('file'))) {

            return ['status' => 0, 'error' => 'Не выбран файл'];

        }

        /** @var Spreadsheet $spreadsheet */
        if (!($spreadsheet = IOFactory::load($file->getPathname()))) {

            return ['status' => 0, 'error' => 'Не удалось загрузить файл'];

        }

        foreach (Tariffs::all() as $tariff) {

            $tariff->delete();

        }

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();

        DB::beginTransaction();

        $tariffRows = [];

        for ($i = 3; $i <= $highestRow; $i++) {

            try {
                $regionName = trim($sheet->getCellByColumnAndRow(2, $i)->getValue());
                $regionCenterName = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());

                if (!$regionName || !$regionCenterName) {

                    break;

                }

                $tariffName = trim($sheet->getCellByColumnAndRow(3, $i)->getValue());
                $min = trim($sheet->getCellByColumnAndRow(4, $i)->getValue()) ?: 0;
                $gb = trim($sheet->getCellByColumnAndRow(5, $i)->getValue()) ?: 0;
                $sms = trim($sheet->getCellByColumnAndRow(6, $i)->getValue()) ?: 0;
                $pricePerDay = trim($sheet->getCellByColumnAndRow(7, $i)->getValue()) ?: 0.00;
                $startBalance = trim($sheet->getCellByColumnAndRow(8, $i)->getValue()) ?: 0.00;
                $unlimitedWhatsApp = trim($sheet->getCellByColumnAndRow(9, $i)->getValue()) ?: 0;
                $unlimitedViber = trim($sheet->getCellByColumnAndRow(10, $i)->getValue()) ?: 0;
                $unlimitedSkype = trim($sheet->getCellByColumnAndRow(11, $i)->getValue()) ?: 0;
                $unlimitedNetwork = trim($sheet->getCellByColumnAndRow(12, $i)->getValue()) ?: 0;
                $categoryName = trim($sheet->getCellByColumnAndRow(13, $i)->getValue());
                $price = trim($sheet->getCellByColumnAndRow(15, $i)->getValue()) ?: 0.00;
                $description = trim($sheet->getCellByColumnAndRow(16, $i)->getValue());

                $region = Regions::where(['name' => $regionName])->first();

                if (!$region) {

                    $region = new Regions();
                    $region->name = $regionName;
                    $region->region_center = $regionCenterName;
                    $region->save();

                }

                if (!empty($categoryName)) {

                    $category = Categories::where(['name' => $categoryName])->first();

                    if (!$category) {

                        $category = new Categories(['name' => $categoryName]);
                        $category->save();

                    }

                }

                $tariff = new Tariffs();
                $tariff->name = $tariffName;
                $tariff->region_id = $region->id;
                $tariff->params = json_encode(['min' => $min, 'gb' => $gb, 'sms' => $sms]);
                $tariff->price_per_day = $pricePerDay;
                $tariff->start_balance = $startBalance;
                $tariff->unlimited = json_encode([
                    'whatsapp' => $unlimitedWhatsApp,
                    'viber' => $unlimitedViber,
                    'skype' => $unlimitedSkype,
                    'network' => $unlimitedNetwork
                ]);
                $tariff->category_id = isset($category) ? $category->id : 0;
                $tariff->price = $price;
                $tariff->description = $description;

                $tariff->save();
            } catch (\PDOException $e) {

                DB::rollBack();

                return ['status' => 0, 'error' => $e->getMessage()];

            }

            $tariffRows[$i] = $tariff->id;

        }

        $imagesPath = public_path() . "/images";

        if (!is_dir($imagesPath)) {

            mkdir($imagesPath, 0775);

        }

        $tariffImages = glob("$imagesPath/*");

        foreach ($tariffImages as $tariffImage) {

            unlink($tariffImage);

        }

        $drawingCollection = $spreadsheet->getActiveSheet()->getDrawingCollection();

        foreach ($drawingCollection as $drawing) {

            $coords = $drawing->getCoordinates();

            preg_match("/(?<row>\d+)/", $coords, $matches);

            $rowNumber = (int)$matches['row'];

            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                ob_start();
                call_user_func(
                    $drawing->getRenderingFunction(),
                    $drawing->getImageResource()
                );
                $imageContents = ob_get_contents();
                ob_end_clean();
                switch ($drawing->getMimeType()) {
                    case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_PNG :
                        $extension = 'png';
                        break;
                    case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_GIF:
                        $extension = 'gif';
                        break;
                    case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_JPEG :
                        $extension = 'jpg';
                        break;
                }
            } else {
                $zipReader = fopen($drawing->getPath(),'r');
                $imageContents = '';
                while (!feof($zipReader)) {
                    $imageContents .= fread($zipReader,1024);
                }
                fclose($zipReader);
                $extension = $drawing->getExtension();
            }

            /** @var Tariffs $tariff */
            $tariff = Tariffs::where(['id' => $tariffRows[$rowNumber]])->first();

            $fileName = sha1($tariff->id) . ".$extension";
            $filePath = $imagesPath . "/" . $fileName;
            $fileUrl = env('APP_URL') . "/images/". $fileName;

            file_put_contents($filePath, $imageContents);

            $tariff->image_link = $fileUrl;
            $tariff->save();

        }



        $ymlFilePaths = glob(public_path() . "/yml/*.xml");

        foreach ($ymlFilePaths as $ymlFilePath) {
            unlink($ymlFilePath);
        }

        DB::commit();

        return ['status' => 1];
    }

    public function exportTariffs(Request $request, TariffsExporter $tariffsExporter)
    {
        $tariffsExporter->doExport();

        return ['link' => env('APP_URL') . "/yml/{$tariffsExporter->getYmlFilename()}"];
    }

    public function checkFeed(Request $request) {

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

        try {

            $tariffEntity->save();

            return ['status' => 1, 'id' => $tariffEntity->id];

        } catch (\PDOException $e) {

            return ['status' => 0, 'error' => $e->getMessage()];

        }

    }
}
