<?php

/** 
 * @author Uniser <uniserpl@gmail.com>
 * @since 2021.04.21 13:36 — 14:38
 */

/**
 * Добавляем архитектурное требование
 */
interface RobotInterface {
    
    public function getHeight();
    
    public function getSpeed();
    
    public function getWeight();
}

/**
 * Простой робот
 * 
 */
class Robot1 implements RobotInterface {
    
    public function getHeight() {
        return 24.3;
    }

    public function getSpeed() {
        return 17.2;
    }

    public function getWeight() {
        return 10.8;
    }
}

class Robot2 implements RobotInterface {
    
    public function getHeight() {
        return 13.1;
    }

    public function getSpeed() {
        return 29.0;
    }

    public function getWeight() {
        return 2.3;
    }
}


/**
 * Фабрика роботов
 */
class FactoryRobot {
    
    private $_robots = [];
    
    /**
     * Добавление нового робота в коллекцию фабрики
     * 
     * @param RobotInterface $robot
     * @throws \Exception
     */
    public function addType(RobotInterface $robot) {
        
        // Слеши в неймспейсах меняем на подчёркивания, чтобы класс робота был доступен через __call
        $class = str_replace('\\','_',get_class($robot));
        
        // В будущем можно игнорировать наличие робота в коллекции
        if (array_key_exists($class, $this->_robots)) {
            throw new \Exception("Robot [$class] already exists in factory's collection");
        }
        
        $this->_robots[$class] = $robot;
    }
    
    /**
     * 
     * @param string $function
     * @param array $args
     * @return RobotInterface
     * @throws \Exception
     */
    public function __call($function, $args) {
        
        if ( ! (substr($function,0,6)==='create' && count($args)===1 && is_int($args[0]) && $args[0]>0)) {
            throw new \Exception("Bad method [$function] or bad argument");
        }
        
        $class = substr($function,6);
        if ( ! array_key_exists($class, $this->_robots)) {
            throw new \Exception("Robot [$class] not present in factory's collection");
        }
        
        
        // Демонстрация функционального стиля, можно было простым циклом
        $robot = $this->_robots[$class];
        return array_map(function()use($robot){
            return clone $robot;
        }, range(1, $args[0]));
    }
        
}

/**
 * Робот рснованный на объединении нескольких простых
 * 
 */
class MergeRobot implements RobotInterface {
    
    // Накопители информации о добавленных роботах
    private $_height = 0.0;
    private $_speed  = INF; //PHP_FLOAT_MAX;
    private $_weight = 0.0;
    
    /**
     * Объединение роботов
     * 
     * @param RobotInterface|RobotInterface[] $robots
     * @throws \Exception
     */
    public function addRobot($robots) {
        
        if (!is_array($robots))
            if ($robots instanceof RobotInterface)
                $robots = [$robots];
            else
                throw new \Exception('Type mismatch. You can add only Robots');
            
        [ $this->_height, $this->_speed, $this->_weight ] =
                
            array_reduce($robots, function($carry, RobotInterface $robot){
                return [
                    $carry[0]+$robot->getHeight(),
                    min([$carry[1],$robot->getSpeed()]),
                    $carry[2]+$robot->getWeight()
                ];
            }, // Начальное значение (Учитываем роботов добавленных раньше)
            [  $this->getHeight(), $this->getSpeed(), $this->getWeight() ]);
    }
    
    public function getHeight() {
        return $this->_height;
    }

    public function getSpeed() {
        return $this->_speed;
    }

    public function getWeight() {
        return $this->_weight;
    }
}

$factory = new FactoryRobot();
// Robot1, Robot2 типи роботів які може створювати фабрика
$factory->addType(new Robot1());
$factory->addType(new Robot2());

//var_dump($factory->createRobot1(5));
//var_dump($factory->createRobot2(2));

$mergeRobot = new MergeRobot();
$mergeRobot ->addRobot(new Robot2());
$mergeRobot ->addRobot($factory->createRobot2(2));
$factory->addType($mergeRobot );
$res = reset($factory->createMergeRobot(1));

var_export([
    'height' => $res->getHeight(),
    'speed'  => $res->getSpeed(),
    'weight' => $res->getWeight()
]);

