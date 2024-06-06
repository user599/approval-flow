<?php

namespace Js3\ApprovalFlow\Console;

use Illuminate\Console\GeneratorCommand;

/**
 * @explain: 生成关联应用方法
 * @author: wzm
 * @date: 2024/6/6 17:07
 * @demo: php artisan make:related-application Department  //
 */
class RelatedApplicationMakeCommand extends GeneratorCommand
{

    protected $name = 'make:related-application';

    protected $description = 'Create a new related application';

    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/related-application.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {

        return $rootNamespace . '\ApprovalFlow\RelatedApplication';
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }
}
