<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Regions;
use App\Tariffs;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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

            return ['tariff' => Tariffs::where(['id' => $id])->first()->toArray()];

        }

        $skip = $request->get('skip');
        $count = $request->get('count');

        $tariffs = Tariffs::query()->with('region');
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

        for ($i = 3; $i <= $highestRow; $i++) {

            try {
                $regionName = trim($sheet->getCellByColumnAndRow(2, $i)->getValue());
                $regionCenterName = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());
                $tariffName = trim($sheet->getCellByColumnAndRow(3, $i)->getValue());
                $min = trim($sheet->getCellByColumnAndRow(4, $i)->getValue()) ?: 0;
                $gb = trim($sheet->getCellByColumnAndRow(5, $i)->getValue()) ?: 0;
                $sms = trim($sheet->getCellByColumnAndRow(6, $i)->getValue()) ?: 0;
                $price = trim($sheet->getCellByColumnAndRow(7, $i)->getValue()) ?: 0.00;
                $startBalance = trim($sheet->getCellByColumnAndRow(8, $i)->getValue()) ?: 0.00;
                $unlimitedWhatsApp = trim($sheet->getCellByColumnAndRow(9, $i)->getValue()) ?: 0;
                $unlimitedViber = trim($sheet->getCellByColumnAndRow(10, $i)->getValue()) ?: 0;
                $unlimitedSkype = trim($sheet->getCellByColumnAndRow(11, $i)->getValue()) ?: 0;
                $unlimitedNetwork = trim($sheet->getCellByColumnAndRow(12, $i)->getValue()) ?: 0;
                $categoryName = trim($sheet->getCellByColumnAndRow(13, $i)->getValue());
                $imageLink = trim($sheet->getCellByColumnAndRow(14, $i)->getValue());
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
                $tariff->price_per_day = $price;
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
                $tariff->image_link = $imageLink;

                $tariff->save();
            } catch (\PDOException $e) {

                DB::rollBack();

                return ['status' => 0, 'error' => $e->getMessage()];

            }

        }

        $ymlFilePath = public_path() . "/yml/export_" . date('Y_m_d') . ".xml";

        if (file_exists($ymlFilePath)) {
            unlink($ymlFilePath);
        }

        DB::commit();

        return ['status' => 1];
    }

    public function exportTariffs(Request $request)
    {
        $publicPath = public_path();

        $ymlPath = "$publicPath/yml";

        if (!is_dir($ymlPath)) {

            mkdir($ymlPath, 0775);

        }

        $ymlFiles = glob("$ymlPath/*.xml");

        foreach ($ymlFiles as $ymlFile) {
            unlink($ymlFile);
        }

        $nowTime = time();

        $ymlFileName = "export_$nowTime.xml";
        $ymlFilePath = "$ymlPath/$ymlFileName";

        $tariffs = Tariffs::all();
        $categories = Categories::all();

        $DOMDocument = new \DOMDocument();
        $DOMDocument->version = '1.0';
        $DOMDocument->encoding = 'utf-8';
        $ymlCatalog = $DOMDocument->createElement('yml_catalog');
        $ymlCatalog->setAttribute('date', date('Y-m-d H:i'));

        $DOMDocument->appendChild($ymlCatalog);

        $shop = $DOMDocument->createElement('shop');

        $currencies = $DOMDocument->createElement('currencies');

        $currency = $DOMDocument->createElement('currency');
        $currency->setAttribute('id', 'RUR');
        $currency->setAttribute('rate', '1');
        $currencies->appendChild($currency);

        $shop->appendChild($currencies);

        $categoriesEl = $DOMDocument->createElement('categories');
        $shop->appendChild($categoriesEl);

        foreach ($categories as $category) {

            $categoryEl = $DOMDocument->createElement('category', htmlentities($category->name));
            $categoryEl->setAttribute('id', $category->id);
            $categoriesEl->appendChild($categoryEl);

        }

        $offers = $DOMDocument->createElement('offers');

        /** @var Tariffs $tariff */
        foreach ($tariffs as $tariff) {

            $offer = $DOMDocument->createElement('offer');
            $offer->setAttribute('id', $tariff->id);
            $offer->setAttribute('available', 'true');

            $offer->appendChild($DOMDocument->createElement('name', htmlentities($tariff->name)));
            $offer->appendChild($DOMDocument->createElement('price', $tariff->price));
            $offer->appendChild($DOMDocument->createElement('description', htmlentities($tariff->description)));
            $offer->appendChild($DOMDocument->createElement('picture', htmlentities($tariff->image_link)));
            $offer->appendChild($DOMDocument->createElement('categoryId', $tariff->category_id));
            $offer->appendChild($DOMDocument->createElement('vendor', 'Билайн'));

            $regionEl = $DOMDocument->createElement('param', htmlentities($tariff->region->name));
            $regionEl->setAttribute('name', Tariffs::PARAMS_LABELS['region']);
            $offer->appendChild($regionEl);

            $params = json_decode($tariff->params, true);

            foreach ($params as $paramKey => $paramValue) {

                $paramEl = $DOMDocument->createElement('param', $paramValue);
                $paramEl->setAttribute('name', Tariffs::PARAMS_LABELS[$paramKey]);
                $offer->appendChild($paramEl);

            }

            $startBalanceEl = $DOMDocument->createElement('param', $tariff->start_balance);
            $startBalanceEl->setAttribute('name', Tariffs::PARAMS_LABELS['start_balance']);
            $offer->appendChild($startBalanceEl);

            $startBalanceEl = $DOMDocument->createElement('param', $tariff->price_per_day);
            $startBalanceEl->setAttribute('name', Tariffs::PARAMS_LABELS['price_per_day']);
            $offer->appendChild($startBalanceEl);

            $offers->appendChild($offer);

            $unlimitedParams = json_decode($tariff->unlimited, true);

            foreach ($unlimitedParams as $paramKey => $paramValue) {

                if ((int)$paramValue) {

                    $paramEl = $DOMDocument->createElement('param', 'Да');
                    $paramEl->setAttribute('name', Tariffs::PARAMS_LABELS[$paramKey]);
                    $offer->appendChild($paramEl);

                }

            }

        }

        $shop->appendChild($offers);
        $ymlCatalog->appendChild($shop);

        $DOMDocument->save($ymlFilePath);

        return ['link' => env('APP_URL') . "/yml/$ymlFileName"];
    }

    public function checkFeed(Request $request) {

        $nowDate = date('Y_m_d');
        $ymlFilePath = "/yml/export_$nowDate.xml";

        if (file_exists(public_path() . $ymlFilePath)) {

            return ['link' => env('APP_URL') . $ymlFilePath];

        }

        return ['link' => null];

    }
}
