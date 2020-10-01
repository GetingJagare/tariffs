<?php

namespace App\Services;

use App\Categories;
use App\TariffFieldTypes;
use App\TariffFieldValues;
use App\Tariffs;

class TariffsExporter
{
    /** @var string  */
    private $ymlPath = "";

    private $ymlFileName = "";

    /**
     * TariffsExporter constructor.
     * @param string $ymlFileName
     */
    public function __construct($ymlFileName = "tariffs_export.xml")
    {
        $publicPath = public_path();

        $this->ymlPath = "$publicPath/yml";

        if (!is_dir($this->ymlPath)) {

            mkdir($this->ymlPath, 0775);

        }

        $this->ymlFileName = $ymlFileName;

    }

    public function checkFile()
    {
        $ymlFiles = glob("{$this->ymlPath}/*.xml");

        foreach ($ymlFiles as $ymlFile) {
            unlink($ymlFile);
        }
    }

    /**
     * @return string
     */
    public function getYmlFilename()
    {
        return $this->ymlFileName;
    }

    public function doExport()
    {

        $this->checkFile();

        $ymlFilePath = "{$this->ymlPath}/{$this->ymlFileName}";

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

            $categoryEl = $DOMDocument->createElement('category', htmlspecialchars($category->name));
            $categoryEl->setAttribute('id', $category->id);
            $categoriesEl->appendChild($categoryEl);

        }

        $offers = $DOMDocument->createElement('offers');

        /** @var Tariffs $tariff */
        foreach ($tariffs as $tariff) {

            $offer = $DOMDocument->createElement('offer');
            $offer->setAttribute('id', $tariff->id);
            $offer->setAttribute('available', 'true');

            $offer->appendChild($DOMDocument->createElement('name', htmlspecialchars($tariff->name)));
            $offer->appendChild($DOMDocument->createElement('price', $tariff->price));
            $offer->appendChild($DOMDocument->createElement('description', htmlspecialchars($tariff->description)));
            $offer->appendChild($DOMDocument->createElement(
                'picture',
                htmlspecialchars(str_replace(["\r\n", "\r", "\n"], "", $tariff->image_link))
            ));
            $offer->appendChild($DOMDocument->createElement('categoryId', $tariff->category_id));
            $offer->appendChild($DOMDocument->createElement('vendor', 'Билайн'));

            $regionEl = $DOMDocument->createElement('param', htmlspecialchars($tariff->region->name));
            $regionEl->setAttribute('name', 'Регион');
            $offer->appendChild($regionEl);

            /** @var TariffFieldValues $fieldValue */
            foreach ($tariff->fieldValues as $fieldValue) {
                if (empty($fieldValue->value)) {
                    continue;
                }

                switch ($fieldValue->field->type->alias) {

                    case TariffFieldTypes::TYPE_CHECKBOX:
                        $value = "Да";
                        break;
                    default:
                        $value = $fieldValue->value;
                        break;

                }

                $paramEl = $DOMDocument->createElement('param', $value);
                $paramEl->setAttribute('name', $fieldValue->field->name);
                $offer->appendChild($paramEl);

            }

            $offers->appendChild($offer);

        }

        $shop->appendChild($offers);
        $ymlCatalog->appendChild($shop);

        $DOMDocument->save($ymlFilePath);

    }
}
