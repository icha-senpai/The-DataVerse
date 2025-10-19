<?php return array (
  'providers' => 
  array (
    0 => 'October\\Rain\\Foundation\\Providers\\AppServiceProvider',
    1 => 'October\\Rain\\Foundation\\Providers\\DateServiceProvider',
    2 => 'October\\Rain\\Database\\DatabaseServiceProvider',
    3 => 'October\\Rain\\Halcyon\\HalcyonServiceProvider',
    4 => 'October\\Rain\\Filesystem\\FilesystemServiceProvider',
    5 => 'October\\Rain\\Html\\UrlServiceProvider',
    6 => 'October\\Rain\\Mail\\MailServiceProvider',
    7 => 'October\\Rain\\Html\\HtmlServiceProvider',
    8 => 'October\\Rain\\Flash\\FlashServiceProvider',
    9 => 'October\\Rain\\Parse\\ParseServiceProvider',
    10 => 'October\\Rain\\Assetic\\AsseticServiceProvider',
    11 => 'October\\Rain\\Resize\\ResizeServiceProvider',
    12 => 'October\\Rain\\Validation\\ValidationServiceProvider',
    13 => 'October\\Rain\\Translation\\TranslationServiceProvider',
    14 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
    15 => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    16 => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    17 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
    18 => 'Illuminate\\Bus\\BusServiceProvider',
    19 => 'Illuminate\\Cache\\CacheServiceProvider',
    20 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
    21 => 'Illuminate\\Cookie\\CookieServiceProvider',
    22 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
    23 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
    24 => 'Illuminate\\Hashing\\HashServiceProvider',
    25 => 'Illuminate\\Pagination\\PaginationServiceProvider',
    26 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
    27 => 'Illuminate\\Queue\\QueueServiceProvider',
    28 => 'Illuminate\\Redis\\RedisServiceProvider',
    29 => 'Illuminate\\Session\\SessionServiceProvider',
    30 => 'Illuminate\\View\\ViewServiceProvider',
    31 => 'Laravel\\Tinker\\TinkerServiceProvider',
    32 => 'Carbon\\Laravel\\ServiceProvider',
    33 => 'Termwind\\Laravel\\TermwindServiceProvider',
    34 => 'October\\Rain\\Foundation\\Providers\\CoreServiceProvider',
    35 => 'System\\ServiceProvider',
  ),
  'eager' => 
  array (
    0 => 'October\\Rain\\Foundation\\Providers\\DateServiceProvider',
    1 => 'October\\Rain\\Database\\DatabaseServiceProvider',
    2 => 'October\\Rain\\Halcyon\\HalcyonServiceProvider',
    3 => 'October\\Rain\\Filesystem\\FilesystemServiceProvider',
    4 => 'October\\Rain\\Html\\UrlServiceProvider',
    5 => 'Illuminate\\Cookie\\CookieServiceProvider',
    6 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
    7 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
    8 => 'Illuminate\\Pagination\\PaginationServiceProvider',
    9 => 'Illuminate\\Session\\SessionServiceProvider',
    10 => 'Illuminate\\View\\ViewServiceProvider',
    11 => 'Carbon\\Laravel\\ServiceProvider',
    12 => 'Termwind\\Laravel\\TermwindServiceProvider',
    13 => 'System\\ServiceProvider',
  ),
  'deferred' => 
  array (
    'october.installer' => 'October\\Rain\\Foundation\\Providers\\AppServiceProvider',
    'command.october.build' => 'October\\Rain\\Foundation\\Providers\\AppServiceProvider',
    'command.october.install' => 'October\\Rain\\Foundation\\Providers\\AppServiceProvider',
    'mail.manager' => 'October\\Rain\\Mail\\MailServiceProvider',
    'mailer' => 'October\\Rain\\Mail\\MailServiceProvider',
    'Illuminate\\Mail\\Markdown' => 'October\\Rain\\Mail\\MailServiceProvider',
    'html' => 'October\\Rain\\Html\\HtmlServiceProvider',
    'form' => 'October\\Rain\\Html\\HtmlServiceProvider',
    'block' => 'October\\Rain\\Html\\HtmlServiceProvider',
    'flash' => 'October\\Rain\\Flash\\FlashServiceProvider',
    'October\\Rain\\Flash\\FlashBag' => 'October\\Rain\\Flash\\FlashServiceProvider',
    'parse.markdown' => 'October\\Rain\\Parse\\ParseServiceProvider',
    'parse.yaml' => 'October\\Rain\\Parse\\ParseServiceProvider',
    'parse.twig' => 'October\\Rain\\Parse\\ParseServiceProvider',
    'parse.ini' => 'October\\Rain\\Parse\\ParseServiceProvider',
    'assetic' => 'October\\Rain\\Assetic\\AsseticServiceProvider',
    'resizer' => 'October\\Rain\\Resize\\ResizeServiceProvider',
    'validator' => 'October\\Rain\\Validation\\ValidationServiceProvider',
    'validation.presence' => 'October\\Rain\\Validation\\ValidationServiceProvider',
    'Illuminate\\Contracts\\Validation\\UncompromisedVerifier' => 'October\\Rain\\Validation\\ValidationServiceProvider',
    'translator' => 'October\\Rain\\Translation\\TranslationServiceProvider',
    'translation.loader' => 'October\\Rain\\Translation\\TranslationServiceProvider',
    'auth.password' => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
    'auth.password.broker' => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
    'command.create.plugin' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.model' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.migration' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.controller' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.component' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.formwidget' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.reportwidget' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.filterwidget' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.contentfield' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.command' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.test' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.job' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.factory' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'command.create.seeder' => 'October\\Rain\\Scaffold\\ScaffoldServiceProvider',
    'Illuminate\\Cache\\Console\\ClearCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Cache\\Console\\ForgetCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'October\\Rain\\Foundation\\Console\\ClearCompiledCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ConfigCacheCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ConfigClearCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\WipeCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\DownCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EnvironmentCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EventCacheCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EventClearCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EventListCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\KeyGenerateCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\OptimizeCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\OptimizeClearCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\PackageDiscoverCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\ClearCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\ListFailedCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\FlushFailedCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\ForgetFailedCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\ListenCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\MonitorCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\PruneBatchesCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\PruneFailedJobsCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\RestartCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\RetryCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\RetryBatchCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\WorkCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'October\\Rain\\Foundation\\Console\\RouteCacheCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\RouteClearCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'October\\Rain\\Foundation\\Console\\RouteListCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleFinishCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleListCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleRunCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleClearCacheCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleTestCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleWorkCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Seeds\\SeedCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\StorageLinkCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\UpCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ViewCacheCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ViewClearCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'October\\Rain\\Foundation\\Console\\ProjectSetCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'October\\Rain\\Foundation\\Console\\ServeCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\VendorPublishCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'migrator' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'migration.repository' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'migration.creator' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Migrations\\Migrator' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\MigrateCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\FreshCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\InstallCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\RefreshCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\ResetCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\RollbackCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\StatusCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\MigrateMakeCommand' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'composer' => 'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Broadcasting\\BroadcastManager' => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
    'Illuminate\\Contracts\\Broadcasting\\Factory' => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
    'Illuminate\\Contracts\\Broadcasting\\Broadcaster' => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
    'Illuminate\\Bus\\Dispatcher' => 'Illuminate\\Bus\\BusServiceProvider',
    'Illuminate\\Contracts\\Bus\\Dispatcher' => 'Illuminate\\Bus\\BusServiceProvider',
    'Illuminate\\Contracts\\Bus\\QueueingDispatcher' => 'Illuminate\\Bus\\BusServiceProvider',
    'Illuminate\\Bus\\BatchRepository' => 'Illuminate\\Bus\\BusServiceProvider',
    'Illuminate\\Bus\\DatabaseBatchRepository' => 'Illuminate\\Bus\\BusServiceProvider',
    'cache' => 'Illuminate\\Cache\\CacheServiceProvider',
    'cache.store' => 'Illuminate\\Cache\\CacheServiceProvider',
    'cache.psr6' => 'Illuminate\\Cache\\CacheServiceProvider',
    'memcached.connector' => 'Illuminate\\Cache\\CacheServiceProvider',
    'Illuminate\\Cache\\RateLimiter' => 'Illuminate\\Cache\\CacheServiceProvider',
    'Illuminate\\Concurrency\\ConcurrencyManager' => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
    'hash' => 'Illuminate\\Hashing\\HashServiceProvider',
    'hash.driver' => 'Illuminate\\Hashing\\HashServiceProvider',
    'Illuminate\\Contracts\\Pipeline\\Hub' => 'Illuminate\\Pipeline\\PipelineServiceProvider',
    'pipeline' => 'Illuminate\\Pipeline\\PipelineServiceProvider',
    'queue' => 'Illuminate\\Queue\\QueueServiceProvider',
    'queue.connection' => 'Illuminate\\Queue\\QueueServiceProvider',
    'queue.failer' => 'Illuminate\\Queue\\QueueServiceProvider',
    'queue.listener' => 'Illuminate\\Queue\\QueueServiceProvider',
    'queue.worker' => 'Illuminate\\Queue\\QueueServiceProvider',
    'redis' => 'Illuminate\\Redis\\RedisServiceProvider',
    'redis.connection' => 'Illuminate\\Redis\\RedisServiceProvider',
    'command.tinker' => 'Laravel\\Tinker\\TinkerServiceProvider',
    'core.composer' => 'October\\Rain\\Foundation\\Providers\\CoreServiceProvider',
  ),
  'when' => 
  array (
    'October\\Rain\\Foundation\\Providers\\AppServiceProvider' => 
    array (
    ),
    'October\\Rain\\Mail\\MailServiceProvider' => 
    array (
    ),
    'October\\Rain\\Html\\HtmlServiceProvider' => 
    array (
    ),
    'October\\Rain\\Flash\\FlashServiceProvider' => 
    array (
    ),
    'October\\Rain\\Parse\\ParseServiceProvider' => 
    array (
    ),
    'October\\Rain\\Assetic\\AsseticServiceProvider' => 
    array (
    ),
    'October\\Rain\\Resize\\ResizeServiceProvider' => 
    array (
    ),
    'October\\Rain\\Validation\\ValidationServiceProvider' => 
    array (
    ),
    'October\\Rain\\Translation\\TranslationServiceProvider' => 
    array (
    ),
    'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider' => 
    array (
    ),
    'October\\Rain\\Scaffold\\ScaffoldServiceProvider' => 
    array (
    ),
    'October\\Rain\\Foundation\\Providers\\ConsoleSupportServiceProvider' => 
    array (
    ),
    'Illuminate\\Broadcasting\\BroadcastServiceProvider' => 
    array (
    ),
    'Illuminate\\Bus\\BusServiceProvider' => 
    array (
    ),
    'Illuminate\\Cache\\CacheServiceProvider' => 
    array (
    ),
    'Illuminate\\Concurrency\\ConcurrencyServiceProvider' => 
    array (
    ),
    'Illuminate\\Hashing\\HashServiceProvider' => 
    array (
    ),
    'Illuminate\\Pipeline\\PipelineServiceProvider' => 
    array (
    ),
    'Illuminate\\Queue\\QueueServiceProvider' => 
    array (
    ),
    'Illuminate\\Redis\\RedisServiceProvider' => 
    array (
    ),
    'Laravel\\Tinker\\TinkerServiceProvider' => 
    array (
    ),
    'October\\Rain\\Foundation\\Providers\\CoreServiceProvider' => 
    array (
    ),
  ),
);