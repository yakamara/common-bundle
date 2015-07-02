<?php

namespace Yakamara\CommonBundle\Command;

use Propel\Generator\Model\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class FormGenerateCommand extends \Propel\PropelBundle\Command\FormGenerateCommand
{
    const COLUMN_IGNORE = ['created_at', 'updated_at'];

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('yakamara:form:generate')
            ->addOption('bundle', null, InputOption::VALUE_REQUIRED, 'The bundle to use to generate Form types (Ex: @AcmeDemoBundle)', '@AppBundle');

        $arguments = $this->getDefinition()->getArguments();
        unset($arguments['bundle']);
        $this->getDefinition()->setArguments($arguments);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        if ($input->hasOption('bundle') && '@' === substr($input->getOption('bundle'), 0, 1)) {
            $this->bundle = $this
                ->getContainer()
                ->get('kernel')
                ->getBundle(substr($input->getOption('bundle'), 1));
        }
    }

    protected function writeFormType(BundleInterface $bundle, Table $table, \SplFileInfo $file, $force, OutputInterface $output)
    {
        $modelName = $table->getPhpName();
        $formTypeContent = file_get_contents(__DIR__ . '/../Resources/skeleton/FormType.php.skeleton');

        $formTypeContent = str_replace('##NAMESPACE##', $bundle->getNamespace() . str_replace('/', '\\', self::DEFAULT_FORM_TYPE_DIRECTORY), $formTypeContent);
        $formTypeContent = str_replace('##CLASS##', $modelName . 'Type', $formTypeContent);
        $formTypeContent = str_replace('##MODEL##', $modelName, $formTypeContent);
        $formTypeContent = str_replace('##TYPE_NAME##', $table->getName(), $formTypeContent);
        $formTypeContent = $this->addFields($table, $formTypeContent);

        file_put_contents($file->getPathName(), $formTypeContent);
        $this->writeNewFile($output, $this->getRelativeFileName($file) . ($force ? ' (forced)' : ''));
    }

    protected function addFields(Table $table, $formTypeContent)
    {
        $buildCode = '';
        foreach ($table->getColumns() as $column) {
            if (!$column->isPrimaryKey() && !in_array($column->getName(), self::COLUMN_IGNORE)) {
                $buildCode .= sprintf("\n            ->add('%s')", lcfirst($column->getPhpName()));
            }
        }

        return str_replace('##BUILD_CODE##', $buildCode, $formTypeContent);
    }
}
