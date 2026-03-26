<?php

declare(strict_types=1);

namespace lee1387\tntrun\bootstrap;

use lee1387\tntrun\TNTRun;

final class TNTRunBootstrap {
    private BootstrapConfigBuilder $configBuilder;
    private BootstrapRuntimeFactory $runtimeFactory;
    private BootstrapRegistrar $registrar;

    public function __construct(TNTRun $plugin) {
        $this->configBuilder = new BootstrapConfigBuilder($plugin);
        $this->runtimeFactory = new BootstrapRuntimeFactory($plugin);
        $this->registrar = new BootstrapRegistrar($plugin);
    }

    public function boot(): void {
        $config = $this->configBuilder->build();
        $runtime = $this->runtimeFactory->create($config);

        $this->registrar->register($config, $runtime);
    }
}
