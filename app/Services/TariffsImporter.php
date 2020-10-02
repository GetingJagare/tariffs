<?php

namespace App\Services;

use App\Categories;
use App\Regions;
use App\Tariffs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TariffsImporter
{
    /** @var Request  */
    private $request;

    /** @var array  */
    private $errors = [];

    /** @var Spreadsheet */
    private $spreadsheet;

    /**
     * TariffsImporter constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function checkFile()
    {

        /** @var UploadedFile $file */
        if (!($file = $this->request->file('file'))) {

            $this->errors[] = 'Не выбран файл';

        }

        /** @var Spreadsheet $spreadsheet */
        if (!($this->spreadsheet = IOFactory::load($file->getPathname()))) {

            $this->errors[] = 'Не удалось загрузить файл';

        }

    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function doImport()
    {

        foreach (Tariffs::all() as $tariff) {

            $tariff->delete();

        }

        $this->spreadsheet->setActiveSheetIndex(0);
        $sheet = $this->spreadsheet->getActiveSheet();

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

        $drawingCollection = $this->spreadsheet->getActiveSheet()->getDrawingCollection();

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

    }
}
