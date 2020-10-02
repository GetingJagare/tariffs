<?php

namespace App\Http\Controllers;

use App\TariffFields;
use App\TariffFieldTypes;
use App\TariffFieldValues;
use Illuminate\Http\Request;

/**
 * Class FieldController
 * @package App\Http\Controllers
 */
class FieldController extends Controller
{
    public function getFields()
    {
        return TariffFields::with(['type'])->get()->toArray();
    }

    public function getFieldTypes()
    {
        return TariffFieldTypes::all(['id', 'name'])->toArray();
    }

    public function addField(Request $request)
    {
        $data = $request->post('field');

        if (empty($data['name']) || empty($data['type'])) {

            return ['error' => 'Заполните все поля'];

        }

        if (TariffFields::where(['name' => $data['name']])->first()) {

            return ['error' => 'Параметр с именем "' . $data['name'] . '" уже существует!'];

        }

        $newField = new TariffFields([
            'name' => $data['name'],
            'tariff_field_types_id' => $data['type']
        ]);
        $newField->save();

        return TariffFields::where(['id' => $newField->id])->with(['type'])->first()->toArray();
    }

    public function deleteFieldValue(Request $request)
    {

        try {

            $fieldValueId = $request->post('field_value_id');
            TariffFieldValues::where(['id' => $fieldValueId])->delete();

        } catch (\PDOException $e) {

            return ['error' => $e->getMessage()];

        }

        return ['status' => 1];

    }

    public function addFieldValue(Request $request)
    {

        try {

            $fieldValueData = $request->post('field_value');

            $fieldValue = new TariffFieldValues();
            $fieldValue->fill($fieldValueData);
            $fieldValue->save();

            return ['status' => 1, 'id' => $fieldValue->id];

        } catch (\PDOException $e) {

            return ['error' => $e->getMessage()];

        }

    }
}
