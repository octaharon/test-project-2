<?php

namespace Providers\Mysql\FieldType;

use Silex\Application;
use Silex\ServiceProviderInterface;

class FieldTypeProvider implements ServiceProviderInterface {

    private $fieldTypes = array();

    private $app;

    /**
     * @param $type
     *
     * @return AbstractFieldType
     *
     * @throws \Exception
     */
    public function getFieldType($type) {

        if ($type === null) {
            $type = AbstractFieldType::TYPE_TEXT;
        }

        if (!isset($this->fieldTypes[$type])) {
            switch ($type) {
                case AbstractFieldType::TYPE_INT:
                    $this->fieldTypes[$type] = new IntFieldType();
                    break;
                case AbstractFieldType::TYPE_FLOAT:
                    $this->fieldTypes[$type] = new FloatFieldType();
                    break;
                case AbstractFieldType::TYPE_TEXT:
                    $this->fieldTypes[$type] = new TextFieldType();
                    break;
                case AbstractFieldType::TYPE_DATE:
                    $this->fieldTypes[$type] = new DateFieldType();
                    break;
                case AbstractFieldType::TYPE_DATETIME:
                    $this->fieldTypes[$type] = new DateTimeFieldType();
                    break;
                case AbstractFieldType::TYPE_TIME:
                    $this->fieldTypes[$type] = new TimeFieldType();
                    break;
                case AbstractFieldType::TYPE_HOURS_INTERVAL:
                    $this->fieldTypes[$type] = new HoursIntervalFieldType();
                    break;
                case AbstractFieldType::TYPE_PAIR:
                    $this->fieldTypes[$type] = new PairFieldType();
                    break;
                case AbstractFieldType::TYPE_JSON:
                    $this->fieldTypes[$type] = new JsonFieldType();
                    break;
                case AbstractFieldType::TYPE_BOOLEAN:
                    $this->fieldTypes[$type] = new BooleanFieldType();
                    break;
                case AbstractFieldType::TYPE_DATETIME_INTERVALS:
                    $this->fieldTypes[$type] = new DateTimeIntervalsFieldType();
                    break;
                default:
                    throw new \Exception('Unknown field type');
                    break;
            }
        }

        return $this->fieldTypes[$type];
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['mysql.field_type_provider'] = $this;
        $this->app = $app;
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}