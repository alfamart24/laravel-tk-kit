<?

namespace alfamart24\laravel_tk_kit;


class Kit
{
    private $token;
    private $apiUrl = 'https://tk-kit.ru/API.1.1';

    /**
     * Kit constructor.
     */
    public function __construct()
    {
        $this->token = config('kit.token');
    }

    /**
     * @param string $func
     * @param array $data
     *
     * @return array
     */
    private function sendRequest(string $func, $data = [])
    {
        $url = $this->apiUrl . '?token=' . $this->token . '&f=' . $func;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, count($data));
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

        for ($i = 0; $i < 5; $i++) {
            $response = curl_exec($curl);
            $headerCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Сервис периодически отправляет битый запрос, если этого не произошло то продолжаем, иначе повторить.
            if ($headerCode != 502)
                break;
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseBody = substr($response, $header_size);
        curl_close($curl);

        return [
          'http_code' => $headerCode,
          'data' => json_decode($responseBody, true)
        ];
    }

    /**
     * Возвращает список населённых пунктов с которыми работает ТК КИТ
     *
     * @return array
     */
    public function getCityList()
    {
        return $this->sendRequest('get_city_list');
    }

    /**
     * Проверяет осуществляется ли доставка в переданный город.
     * Если доставка в переданный город не осуществляется то возвращает false
     *
     * @param string $city
     *
     * @return array|bool
     */
    public function isCity(string $city)
    {
        $cityData = $this->sendRequest('is_city', ['city' => $city]);

        if ($cityData['data'] === [0]) {
            return false;
        }

        $data = explode(':', $cityData['data'][0]);
        $vals = ['COUNTRY', 'REGION', 'TZONEID', 'ID', 'SR'];
        $cityData = [];

        foreach ($data as $key => $value) {
            $cityData[$vals[$key]] = $value;
        };

        return $cityData;

    }

    /**
     * Возвращает стоимость и срок перевозки по указанному маршруту
     *
     * @param array $data
     * @param string $city_from
     * @param string $city_to
     *
     * @return array
     */
    public function priceOrder(array $data, string $city_from, string $city_to)
    {
        if (!$city_from = $this->isCity($city_from)) {
            return ['error' => 'Не работаем с '. $city_from];
        }
        if (!$city_to = $this->isCity($city_to)) {
            return ['error' => 'Не работаем с '. $city_to];
        }

        $data['SLAND'] = $city_from['COUNTRY'];
        $data['SCODE'] = $city_from['ID'];
        $data['SZONE'] = $city_from['TZONEID'];
        $data['SREGIO'] = $city_from['REGION'];

        $data['RSLAND'] = $city_to['COUNTRY'];
        $data['RCODE'] = $city_to['ID'];
        $data['RZONE'] = $city_to['TZONEID'];
        $data['RREGIO'] = $city_to['REGION'];

        return $this->sendRequest('price_order', $data);
    }

    /**
     * Возвращает стоимость и срок перевозки по указанному маршруту
     * Slim - без проверки городов, на вход подаются данные пришедшие из isCity
     * @param array $data
     * @param array $city_from
     * @param array $city_to
     *
     * @return array
     */
    public function priceOrderSlim(array $data, array $city_from, array $city_to)
    {
        $data['SLAND'] = $city_from['COUNTRY'];
        $data['SCODE'] = $city_from['ID'];
        $data['SZONE'] = $city_from['TZONEID'];
        $data['SREGIO'] = $city_from['REGION'];

        $data['RSLAND'] = $city_to['COUNTRY'];
        $data['RCODE'] = $city_to['ID'];
        $data['RZONE'] = $city_to['TZONEID'];
        $data['RREGIO'] = $city_to['REGION'];
        return $this->sendRequest('price_order', $data);
    }

    /**
     * Получение статуса заявки по номеру Экспедиторской Расписки
     *
     * @param string $number
     *
     * @return array
     */
    public function checkStat(string $number)
    {
        return $this->sendRequest('checkstat', $number);
    }

    /**
     * Возвращает список страховых компаний с которыми сотрудничает ТК КИТ
     *
     * @return array
     */
    public function getInsuranceAgents()
    {
        return $this->sendRequest('get_insurance_agents');
    }

    /**
     * Список дополнительных услуг для Интернет Магазинов
     *
     * @return array
     */
    public function getImServices()
    {
        return $this->sendRequest('get_im_services');
    }

    /**
     * Общий список дополнительных услуг
     *
     * @return array
     */
    public function getServices()
    {
        return $this->sendRequest('get_services');
    }

    /**
     * Возвращает список складов в городах, через которые осуществляется междугородняя перевозка.
     * Ключ массива - это ID населенного пункта (см. описание функции get_city_list)
     *
     * @return array
     */
    public function getRp()
    {
        return $this->sendRequest('get_rp');
    }

    /**
     * Возвращает подробную информацию о заказе
     *
     * @param array $data
     * @return array
     */
    public function getOrderInfo($data)
    {
        return $this->sendRequest('get_order_info', $data);
    }

    /**
     * Возвращает хеш пароля(специальную криптографическую форму контрольной суммы).
     * Хэш используется для некоторых других методов API.
     *
     * @param array $data
     * @return array
     */
    public function getHash($data = ['ILOGIN' => '', 'IPASSWORD' => ''])
    {
        return $this->sendRequest('get_hash', $data);
    }

    /**
     * Возвращает список актов выполненных работ.
     *
     * @param array $data
     * @return array
     */
    public function getAvr($data)
    {
        return $this->sendRequest('get_avr', $data);
    }

    /**
     * Запрос на создание новой заявки на перевозку.
     *
     * @param array $data
     * @return array
     */
    public function createOrder($data)
    {
        return $this->sendRequest('create_order', $data);
    }
}