<?php

namespace MrCat\SugarCrmWrapper;

class SugarCrmWrapper
{
    /**
     * New instance class.
     *
     * @var $this
     */
    private static $instance = null;

    /**
     * Llave de session para el accesso de los metodos rest del sugar crm.
     *
     * @var string
     */
    protected $session = '';

    /**
     * Parametros necesarios para el accesso de los metodos rest de la aplicaicon sugar crm.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Obteniendo los errores de la aplicacion de sugar crm al momento de realizar la peticion.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Id Del usuario que se encuentra en session.
     *
     * @var string
     */
    protected $id;

    /**
     * Un usuario inicia sesión en la aplicación de sugar crm.
     *
     * @param array $credentials
     *
     * @return boolean
     */
    public function login(array $credentials = [])
    {
        //validando las credenciales.
        if ($this->validateCredentials($credentials)) {
            // formulario para la autenticacion a la api del sugarcrm.
            $this->setFormParams([
                'user_auth' => [
                    'user_name' => $credentials['username'],
                    'password' => md5($credentials['password']),
                ],
                'name_value_list' => [
                    [
                        'name' => 'notifyonsave',
                        'value' => 'true',
                    ],
                ],
            ]);

            // enviado la peticion del metodo login al sugar crm con sus parametros..
            $request = Request::send('login', $this->parameters);

            // validando los errores de la solicitud.
            $this->validateErrors($request);

            // setea un nuevo valor para la session.
            $this->setSession($request['id']);

            // transformando los valores de la respuesta de los metodos de la aplicacion.
            $request = $this->transformResponseValues($request['name_value_list']);

            //setea un nuevo valor para el usuario que se encuentra en session.
            $this->setId($request['user_id']);

            return true;
        }

        return false;
    }

    /**
     * Recupera el acceso OAuth Token
     *
     * @param $sesion
     *
     * @return array
     */
    public function oauthAccess($sesion)
    {
        // formulario para la solicitud del metodo oauth_access
        $this->setFormParams([
            'session' => $sesion,
        ]);

        // enviado la peticion del metodo oauth_access al sugar crm con sus parametros..
        $request = Request::send('oauth_access', $this->parameters);

        // validando los errores de la solicitud.
        $this->validateErrors($request);

        return $request;
    }

    /**
     * Obtiene el id del usuario en session
     *
     * @return $this
     */
    public function getUserId()
    {
        // formulario para la solicitud del metodo get_user_id
        $this->setFormParams([
            'session' => $this->getSession(),
        ]);

        // enviado la peticion del metodo logout al sugar crm con sus parametros..
        $request = Request::send('get_user_id', $this->parameters);

        //setea un nuevo valor para el usuario que se encuentra en session.
        $this->setId($request);

        return $request;
    }

    /**
     * Elimina el accesso de la session para la aplicacion.
     *
     * @return $this
     */
    public function logout()
    {
        // formulario para la solicitud del metodo logout
        $this->setFormParams([
            'session' => $this->getSession(),
        ]);

        // enviado la peticion del metodo logout al sugar crm con sus parametros..
        $request = Request::send('logout', $this->parameters);

        // validando los errores de la solicitud.
        $this->validateErrors($request);

        // setea un nuevo valor para la session.
        $this->setSession(null);

        return $this;
    }

    /**
     * Recupera la lista campos para un módulo específico.
     *
     * @param string $module
     *
     * @return array
     */
    public function getModuleFields($module)
    {
        // formulario para la solicitud del metodo get_module_fields
        $this->setFormParams([
            'session' => $this->getSession(),
            'module_name' => $module,
        ]);

        // enviado la peticion del metodo get_module_fields al sugar crm con sus parametros..
        $request = Request::send('get_module_fields', $this->parameters);

        // validando los errores de la solicitud.
        $this->validateErrors($request);

        return $request;
    }

    /**
     * Obtiene todos los modulos del sistema.
     *
     * @return mixed
     */
    public function getAvaliableModules()
    {
        // formulario para la solicitud del metodo get_available_modules
        $this->setFormParams([
            'session' => $this->getSession(),
        ]);

        // enviado la peticion del metodo get_available_modules al sugar crm con sus parametros..
        $request = Request::send('get_available_modules', $this->parameters);

        // validando los errores de la solicitud.
        $this->validateErrors($request);

        return $request;
    }

    /**
     * Recupera un solo registro basado en ID de registro.
     *
     * @param string $module
     * @param string $id
     * @param array $options
     *
     * @return array
     */
    public function getEntry($module, $id, $options = [])
    {
        // Validando las opciones del metodo
        $options = $this->validateOptionsMethodGetEntry($options);

        // formulario para la solicitud del metodo get_entry
        $this->setFormParams([
            'session' => $this->getSession(),
            'module_name' => $module,
            'id' => $id,
            'select_fields' => $options['select_fields'],
            'link_name_to_fields_array' => $options['link_name_to_fields_array'],
        ]);

        // enviado la peticion del metodo logout al sugar crm con sus parametros..
        $request = Request::send('get_entry', $this->parameters);

        // validando los errores de la solicitud.
        $this->validateErrors($request);

        // transformando los valores de la respuesta de los metodos de la aplicacion.
        $request = $this->transformResponse($request);

        return $request[0];
    }

    /**
     * Creando o actualizando un registro.
     *
     * @param string $module
     * @param array $data
     *
     * @return mixed
     *
     * @return string
     */
    public function setEntry($module, array $data = [])
    {
        // formulario para la solicitud del metodo set_entry
        $this->setFormParams([
            'session' => $this->getSession(),
            'module_name' => $module,
            'name_value_list' => $this->helpers()->requestValue($data),
        ]);

        // enviado la peticion del metodo set_entry al sugar crm con sus parametros..
        $request = Request::send('set_entry', $this->parameters);

        // validando los errores de la solicitud.
        $this->validateErrors($request);

        return $request['id'];
    }

    /**
     * Retrieves a list of beans based on query specifications.
     *
     * @param string $module
     * @param array $options
     *
     * @return mixed
     */
    public function getEntryList($module, array $options = [])
    {
        // Validando las opciones del metodo
        $options = $this->validateOptionsMethodGetEntry($options);

        // formulario para la solicitud del metodo get_entry_list
        $this->setFormParams([
            'session' => $this->getSession(),
            'module_name' => $module,
            'query' => $options['query'],
            'order_by' => $options['order_by'],
            'offset' => $options['offset'],
            'select_fields' => $options['select_fields'],
            'link_name_to_fields_array' => $options['link_name_to_fields_array'],
            'max_results' => $options['max_results'],
            'deleted' => $options['deleted'],
        ]);

        // enviado la peticion del metodo get_entry_list al sugar crm con sus parametros..
        $request = Request::send('get_entry_list', $this->parameters);

        // validando los errores de la solicitud.
        $this->validateErrors($request);

        // transformando los valores de la respuesta de los metodos de la aplicacion.
        $data = $this->transformResponse($request);

        if ($data) {
            return [
                'count' => [
                    'result_count' => $request['result_count'],
                    'total_count' => $request['total_count'],
                ],
                'data' => $data,
            ];
        }
    }

    /**
     * Recupera una lista de registros en base a las especificaciones de la consulta.
     *
     * @param string $module
     * @param array $data
     *
     * @return mixed
     */
    public function setEntries($module, array $data = [])
    {
        // formulario para la solicitud del metodo set_entries
        $this->setFormParams([
            'session' => $this->getSession(),
            'module_name' => $module,
            'name_value_list' => $this->helpers()->requestValueMultiple($data),
        ]);

        // enviado la peticion del metodo set_entries al sugar crm con sus parametros..
        $request = Request::send('set_entries', $this->parameters);

        // validando los errores de la solicitud.
        $this->validateErrors($request);

        return $request;
    }

    /**
     * Crea un documento mediante set_entry y una revisión de documentos con el método set_document_revision
     *
     * @param $module
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function setDocument($module, array $data = [])
    {
        // creando un registro
        $document = $this->setEntry($module, $data);

        // validando los documentos
        $data = $this->validateDocuments($data);

        // formulario para la solicitud del metodo set_document_revision
        $this->setFormParams([
            'session' => $this->getSession(),
            'note' => [
                'id' => $document['id'],
                'file' => base64_encode(file_get_contents($data['file'])),
                'filename' => $data['filename'],
                'version' => $data['version'],
            ],
        ]);

        // enviado la peticion del metodo set_document_revision al sugar crm con sus parametros..
        $request = Request::send('set_document_revision', $this->parameters);

        // validando los errores de la solicitud.
        $this->validateErrors($request);

        return $request;
    }

    /**
     * Validando opciones del documento
     *
     * @param $data
     * @return mixed
     * @throws SugarCrmWrapperException
     */
    private function validateDocuments($data)
    {
        $defaultNoteDocuments = ['file', 'name', 'filename'];

        if (isset($data['note_documents'])) {
            foreach ($data['note_documents'] as $key => $value) {
                if (!array_key_exists($key, $defaultNoteDocuments)) {
                    throw new SugarCrmWrapperException('Not Found Key ' . $key);
                }
            }

            return $data;
        }
    }

    /**
     * Validando las opciones para el metodo de la aplicacion getEntry
     *
     * @param array $options
     * @return array
     */
    private function validateOptionsMethodGetEntry(array $options = [])
    {
        // transformando valores para las opciones de las relaciones del metodo.
        $options['link_name_to_fields_array'] = $this->helpers()->requestValueRelations($options);

        $default = [
            'query' => '',
            'order_by' => '',
            'offset' => 0,
            'select_fields' => [],
            'link_name_to_fields_array' => [],
            'max_results' => 0,
            'deleted' => false,
        ];

        return array_merge($default, $options);
    }

    /**
     * Obtiene el Id del usuario que se encuentra en session.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setea un nuevo valor al Id del usuario que se encontrara en session.
     *
     * @param $id
     * @return $this;
     */
    public function setId($id = '')
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Transformando los valores de la respuesta de los metodos de la aplicacion de sugarcrm.
     *
     * @param array $request
     * @return array
     */
    public function transformResponseValues(array $request = [])
    {
        return $this->helpers()->responseValue($request);
    }

    /**
     * Obtiene la session para el acceso a los metodos de la aplicacion de sugar crm.
     *
     * @return string
     */
    public function getSession()
    {
        if ($this->hasSession()) {
            return $this->session;
        }
    }

    /**
     * Verifica si existe una session en el sistema.
     *
     * @return bool
     */
    public function hasSession()
    {
        return !is_null($this->session);
    }

    /**
     * Seteando nuevo valor para la session.
     *
     * @param $session
     * @return $this
     */
    public function setSession($session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Validar las credenciales para el accesso a la aplicacion de sugar crm.
     *
     * @param $credentials
     * @return bool
     */
    private function validateCredentials($credentials)
    {
        if (!isset($credentials['username'], $credentials['password'])) {
            return false;
        }

        return true;
    }

    /**
     * Seteando los parametros del formulario para la solicitud de los metodos de la aplicacion de sugar crm.
     *
     * @param array $form
     * @return array
     */
    private function setFormParams(array $form = [])
    {
        return $this->parameters['form_params'] = $form;
    }

    /**
     * Seteando los errores obtenidos al momento de realizar la peticion de los metodos de la aplicacion del sugar crm.
     *
     * @param array $errors
     * @return array
     */
    private function setErrors($errors)
    {
        return $this->errors = $errors;
    }

    /**
     * Validando los errores de peticion a los metodos de la aplicacion del sugar crm
     *
     * @param array $request
     * @return bool
     * @throws SugarCrmWrapperException
     */
    private function validateErrors(array $request = [])
    {
        if (isset($request['name'], $request['number'], $request['description'])) {
            $this->setErrors($request);
            throw new SugarCrmWrapperException($request['name']);
        }
    }

    /**
     * Set Response Array EntryList
     *
     * @param $request
     * @return array
     */
    private function transformResponse($request)
    {
        $records = [];
        if (isset($request['entry_list'])) {
            foreach ($request['entry_list'] as $i => $entry) {
                //agregando registros.
                $records[] = array_merge(
                // tranformando los valores de la peticion.
                    $this->transformResponseValues($entry['name_value_list']),
                    // agregando las relaciones
                    $this->setRelations($request, $i)
                );
            }
        }
        return $records;
    }

    /**
     * Agrega las relaciones de los metodos get_entry y get_entries
     *
     * @param $request
     * @param $i
     * @return array
     */
    private function setRelations($request, $i)
    {
        $results = [];
        //seteando las relaciones por el metodo get_entries
        if (isset($request['relationship_list'][$i]['link_list'])) {
            foreach ($request['relationship_list'][$i]['link_list'] as $module) {
                foreach ($module['records'] as $x => $record) {
                    $results[$module['name']] = $this->transformResponseValues($record['link_value']);
                }
            }
            return $results;
        }

        //seteando las relaciones por el metodo get_entry
        if (isset($request['relationship_list'][0])) {
            foreach ($request['relationship_list'][0] as $module) {
                foreach ($module['records'] as $x => $record) {
                    $results[$module['name']] = $this->transformResponseValues($record);
                }
            }
            return $results;
        }

        return $results;
    }

    /**
     * Instance Helpers Class
     *
     * @return Helpers
     */
    private function helpers()
    {
        return Helpers::get();
    }

    /**
     * Gets the instance via lazy initialization (created on first usage).
     *
     * @return self
     */
    public static function get()
    {
        return self::$instance;
    }

    /**
     * Instance new Api with config.
     *
     * @param array $parameters
     *
     * @return static
     */
    public static function config(array $parameters)
    {
        if (null === static::$instance) {
            static::$instance = new static($parameters);
        }

        return static::$instance;
    }

    /**
     * SugarCrmMethod constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Prevent the instance from being cloned.
     *
     * @throws \Exception
     *
     * @return void
     */
    final public function __clone()
    {
        throw new SugarCrmWrapperException('This is a Singleton. Clone is forbidden');
    }

    /**
     * Prevent from being unserialized.
     *
     * @throws \Exception
     *
     * @return void
     */
    final public function __wakeup()
    {
        throw new SugarCrmWrapperException('This is a Singleton. __wakeup usage is forbidden');
    }
}
