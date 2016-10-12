<?php

namespace MrCat\SugarCrmWrapper;

class Helpers
{
    /**
     * Instance new Helpers
     *
     * @return Helpers
     */
    public static function get()
    {
        return new static();
    }

    /**
     * Converts a SugarCrm-REST compatible name_value_list to an Array
     *
     * @param $data
     *
     * @return array
     */
    public function responseValue(array $data = [])
    {
        $return = [];
        foreach ($data as $row) {
            $return[$row['name']] = $this->replaceOptionsMultipleArray(
                $row['value']
            );
        }
        return $return;
    }

    /**
     * replace ^ string
     *
     * @param string $data
     * @return array
     */
    private function replaceOptionsMultipleArray($data)
    {
        if (substr($data, 0, 1) == '^' && substr($data, -1) == '^') {
            return $this->multiexplode(['^,^', '^'], $data);
        }

        return $data;
    }

    /**
     * Convert string to array.
     *
     * @param $delimiters
     * @param $string
     * @return array
     */
    private function multiexplode($delimiters, $string)
    {
        $ready = str_replace($delimiters, '|', $string);

        return array_filter(explode('|', $ready), 'strlen');
    }

    /**
     * Converts a SuiteCrm-REST compatible name_value to an Array
     *
     * @param array $data
     *
     * @return array
     */
    public function requestValueMultiple(array $data = [])
    {
        $return = [];
        for ($i = 0; $i < count($data); $i++) {
            if (is_array($data[$i])) {
                foreach ($data[$i] as $key => $value) {
                    $return[$i][] = [
                        'name'  => $key,
                        'value' => $this->replaceOptionsMultipleString($value),
                    ];
                }
            }
        }

        return $return;
    }

    /**
     * Converts a SuiteCrm-REST compatible name_value to an Array
     *
     * @param array $options
     *
     * @return array
     */
    public function requestValueRelations(array $options = [])
    {
        $return = [];
        foreach ($options as $key) {
            foreach ($key as $i => $value) {
                $return[] = [
                    'name'  => $i,
                    'value' => $value,
                ];
            }
        }

        return $return;
    }

    /**
     * Converts a SuiteCrm-REST compatible name_value to an Array
     *
     * @param array $data
     *
     * @return array
     */
    public function requestValue($data)
    {
        $data = str_replace("&", "%26", $data);
        $return = [];
        foreach ($data as $key => $value) {
            $return[] = [
                'name'  => $key,
                'value' => $this->replaceOptionsMultipleString($value),
            ];
        }

        return $return;
    }

    /**
     * replace ^ string
     *
     * @param $data
     *
     * @return string
     */
    private function replaceOptionsMultipleString($data)
    {
        if (is_array($data)) {
            $data = array_map(function ($value) {
                return '^' . $value . '^';
            }, $data);

            return implode(',', $data);
        }
        return $data;
    }
}
