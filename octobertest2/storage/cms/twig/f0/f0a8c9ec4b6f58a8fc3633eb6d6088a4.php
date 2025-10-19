<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* C:\laragon\www\active\octobertest2\themes/dataverse/pages/404.htm */
class __TwigTemplate_4dd6a2c1398ddba2c5543ecffcbc5a24 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!DOCTYPE html>
<html lang=\"en\" class=\"h-full bg-[#080816] text-gray-200\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
  <title>404 | The DataVerse</title>
  <link href=\"/assets/css/output.css\" rel=\"stylesheet\">
</head>
<body class=\"h-full flex flex-col items-center justify-center text-center\">
  <div class=\"relative\">
    <div class=\"absolute inset-0 blur-3xl bg-gradient-to-r from-fuchsia-500/40 to-cyan-400/40 animate-pulse rounded-full w-[300px] h-[300px] mx-auto\"></div>
    <h1 class=\"text-[6rem] font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-500 to-cyan-400 drop-shadow-[0_0_20px_rgba(255,0,255,0.3)]\">
      404
    </h1>
  </div>

  <p class=\"mt-4 text-lg text-gray-400 max-w-md\">
    The Data Stream fractured. The page you seek drifted into a digital void.
  </p>

  <a href=\"/\" class=\"mt-8 inline-block px-6 py-3 rounded-full font-semibold text-[#080816] bg-gradient-to-r from-cyan-400 to-fuchsia-500 hover:opacity-90 transition\">
    Return to Reality
  </a>

  <div class=\"absolute bottom-8 text-sm text-gray-600\">
    <span class=\"font-mono\">ERR_DATAVERSE_NOT_FOUND</span>
  </div>
</body>
</html>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/pages/404.htm";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"en\" class=\"h-full bg-[#080816] text-gray-200\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
  <title>404 | The DataVerse</title>
  <link href=\"/assets/css/output.css\" rel=\"stylesheet\">
</head>
<body class=\"h-full flex flex-col items-center justify-center text-center\">
  <div class=\"relative\">
    <div class=\"absolute inset-0 blur-3xl bg-gradient-to-r from-fuchsia-500/40 to-cyan-400/40 animate-pulse rounded-full w-[300px] h-[300px] mx-auto\"></div>
    <h1 class=\"text-[6rem] font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-500 to-cyan-400 drop-shadow-[0_0_20px_rgba(255,0,255,0.3)]\">
      404
    </h1>
  </div>

  <p class=\"mt-4 text-lg text-gray-400 max-w-md\">
    The Data Stream fractured. The page you seek drifted into a digital void.
  </p>

  <a href=\"/\" class=\"mt-8 inline-block px-6 py-3 rounded-full font-semibold text-[#080816] bg-gradient-to-r from-cyan-400 to-fuchsia-500 hover:opacity-90 transition\">
    Return to Reality
  </a>

  <div class=\"absolute bottom-8 text-sm text-gray-600\">
    <span class=\"font-mono\">ERR_DATAVERSE_NOT_FOUND</span>
  </div>
</body>
</html>", "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/pages/404.htm", "");
    }
}
