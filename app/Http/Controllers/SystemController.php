<?php

namespace App\Http\Controllers;

use App\Regions;
use App\Tariffs;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
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
     * @param null|int $skip
     * @param null|int $count
     * @return array
     */
    public function getTariffs($skip = null, $count = null)
    {

        $tariffs = Tariffs::all();

        if ($skip) {
            $tariffs->skip($skip);
        }

        if ($count) {
            $tariffs->chunk($count);
        }

        return $tariffs->toArray();
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

            return ['status' => 0];

        }

        /** @var Spreadsheet $spreadsheet */
        if (!($spreadsheet = IOFactory::load($file->getPathname()))) {

            return ['status' => 0];

        }

        foreach (Tariffs::all() as $tariff) {

            $tariff->delete();

        }

        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();

        for ($i = 0; $i <= $highestRow; $i++) {

            $regionName = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());
            $tariffName = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());

            $region = Regions::where(['name' => $regionName])->first();

            if (!$region) {

                $region = new Regions();
                $region->name = $regionName;
                $region->save();

            }

            $tariff = new Tariffs();
            $tariff->name = $tariffName;
            $tariff->region_id = $region->id;

            $tariff->save();

        }
    }
}
