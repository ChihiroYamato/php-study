<?php

namespace App\Base\Exceptions;

use App\Base\Helpers\Traits\TraitDirectory;
use App\Base\Helpers\Traits\SimpleExceptionHandler;
use Exception;
use DateTime;
use DOMDocument;
use SimpleXMLElement;

/**
 * Абстрактный класс для всех Exception проекта/
 * Поддерживает сохранение ошибки в xml файл и форматированный вывод в html
 *
 * @var ?string $className [static] имя текущего класса Exception
 * @var DateTime $curentTime время возникновения Exception
 * @var string $logsPath путь к файлу логов
 * @var string XML_ROOT_ELEMENT [const] имя корневого XML элемента
 * @var array HTML_EXCEPTION_CLASS [const] массив css классов для html вывода ошибок
 *
 * @method __construct :void string $message, int $code, ?\Throwable $previous, string $logsName
 * @method getClassName :string
 * @method toLogsXML :bool
 * @method toStringHTML :string
 * @method initialLogsXML :ProjectException
 * @method formatLogsXML :ProjectException
 * @method addExceptionToXML :ProjectException
 */
abstract class ProjectException extends Exception
{
    use TraitDirectory, SimpleExceptionHandler;

    /** @var DateTime $curentTime время возникновения Exception */
    protected DateTime $curentTime;

    /** @var string $logsPath путь к файлу логов */
    protected string $logsPath;

    /** @var ?string $className [static] имя текущего класса Exception */
    protected static ?string $className = null;

    /** @var string XML_ROOT_ELEMENT [const] имя корневого XML элемента */
    protected const XML_ROOT_ELEMENT = 'Exceptions';

    /** @var array HTML_EXCEPTION_CLASS [const] массив css классов для html вывода ошибок */
    protected const HTML_EXCEPTION_CLASS = [
        'ALERT' => 'alert alert-danger padding-around',
    ];

    /**
     * Метод инициализирует объект дочернего класса от ProjectException
     * @param string $message [optional] сообщение ошибки
     * @param int $code [optional] код ошибки
     * @param ?\Throwable $previous [optional] предыдущее исключение, используется при создания цепочки исключений
     * @param string $logsName [optional] имя файла для логов
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, string $logsName = EXCEPTION_LOGS_NAME)
    {
        parent::__construct($message, $code, $previous);
        $this->curentTime = new DateTime();
        $this->logsPath = LOGS_PATH . $logsName;
        if (self::$className === null) {
            self::$className = preg_match('/([\w]*Exception)$/', get_class($this), $matches) ? $matches[1] : 'Undefined';
        }
    }

    /**
     * Метод возвращает имя текущего класса Exception
     * @return string имя текущего класса Exception
     */
    public function getClassName() : string
    {
        return self::$className;
    }

    /**
     * Метод записывает Exception в XML файл по указанному пути
     * @param string $logsName [optional] имя файла для логов
     * @return bool возвращает результат записи ошибки в XML файл
     */
    public function toLogsXML() : bool
    {
        try {
            $this->initialLogsXML()->addExceptionToXML()->formatLogsXML();
        } catch (SimpleException $error) {
            self::sendEmergencyMail($error);
            return false;
        }

        return true;
    }

    /**
     * Метод возвращает информацию об Exception в формате HTML
     * @return string сообщение об ошибке, отформатированное для вывода в HTML
     */
    public function toStringHTML() : string
    {
        $html = new DOMDocument();

        $exception = $html->createElement('div');

        $class = $html->createAttribute('class');
        $class->value = self::HTML_EXCEPTION_CLASS['ALERT'];

        $exception->appendChild($class);
        $exception->appendChild($html->createElement('span', $this->message));

        $result = $html->saveHTML($exception);

        return $result !== false ? $result : '';
    }

    /**
     * Метод проверяет существование стандартного файла для хранения Exceptions в формате XML
     * и создает его в случае отсутствия
     * @return ProjectException возвращает экземпляр текущего исключения
     * @throw SimpleException
     */
    protected function initialLogsXML() : ProjectException
    {
        if (! file_exists($this->logsPath)) {
            self::makeDirectory(dirname($this->logsPath));

            $xml = new DOMDocument('1.0', 'utf-8');
            $xml->appendChild($xml->createElement(self::XML_ROOT_ELEMENT));

            if (! $xml->save($this->logsPath)) {
                throw new SimpleException("Ошибка сохранения xml в {$this->logsPath}");
            }
        }

        return $this;
    }

    /**
     * Метод форматирует указанный XML файл
     * @return ProjectException возвращает экземпляр текущего исключения
     * @throw SimpleException
     */
    protected function formatLogsXML() : ProjectException
    {
        $xml = new DOMDocument();
        $xml->formatOutput = true;
        if (! $xml->load($this->logsPath, LIBXML_NOBLANKS)) {
            throw new SimpleException("Ошибка загрузки xml из {$this->logsPath}");
        }
        if (! $xml->save($this->logsPath)) {
            throw new SimpleException("Ошибка сохранения xml в {$this->logsPath}");
        }

        return $this;
    }

    /**
     * Метод добавляет информацию по текущему исключению в увказанный XML файл
     * @return ProjectException возвращает экземпляр текущего исключения
     * @throw SimpleException
     */
    protected function addExceptionToXML() : ProjectException
    {
        if (($xmlFile = file_get_contents($this->logsPath)) === false) {
            throw new SimpleException("Ошибка загрузки xml из {$this->logsPath}");
        }

        $xml = new SimpleXMLElement($xmlFile);
        $id = strtolower(self::$className) . $this->code . $this->curentTime->format('YmdHis');

        if (! isset($xml->{self::$className})) {
            $xml->addChild(self::$className);
        }

        $exception = $xml->{self::$className}->addChild('Exception');
        $exception->addAttribute('id', $id);
        $exception->addChild('Code', $this->code);
        $exception->addChild('Date', $this->curentTime->format('Y-m-d'));
        $exception->addChild('Time', $this->curentTime->format('H:i:s'));
        $exception->addChild('Massage', $this->message);
        $exception->addChild('File', $this->file);
        $exception->addChild('Line', $this->line);

        if ($xml->asXML($this->logsPath) === false) {
            throw new SimpleException("Ошибка сохранения xml в {$this->logsPath}");
        }

        return $this;
    }
}
