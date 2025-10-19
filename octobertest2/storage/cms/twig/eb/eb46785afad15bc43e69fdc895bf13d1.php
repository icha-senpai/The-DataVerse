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

/* C:\laragon\www\active\octobertest2\themes/dataverse/layouts/default.htm */
class __TwigTemplate_b7f92ef209b309d5aec492b23def9671 extends Template
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
<html lang=\"en\" class=\"tv dark\" data-theme=\"dataverse\">
  <head>
    <meta charset=\"utf-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <title>";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, ($context["this"] ?? null), "page", [], "any", false, false, false, 6), "title", [], "any", false, false, false, 6), "html", null, true);
        yield " - The DataVerse</title>

    ";
        // line 8
        $_minify = System\Classes\CombineAssets::instance()->useMinify;
        yield '<script src="' . Request::getBasePath() . '/modules/system/assets/js/framework-extras'.($_minify ? '.min' : '').'.js"></script>' . PHP_EOL;
        yield '<link rel="stylesheet" property="stylesheet" href="' . Request::getBasePath() .'/modules/system/assets/css/framework-extras.css">' . PHP_EOL;
        unset($_minify);
        // line 9
        yield "    ";
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->assetsFunction('css');
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->displayBlock('styles');
        // line 10
        yield "    <link rel=\"stylesheet\" href=\"";
        yield $this->extensions['Cms\Twig\Extension']->themeFilter("assets/vendor/tailwind/tailwind.css");
        yield "\">
  </head>

  <body class=\"bg-dv-bg text-dv-text min-h-screen flex flex-col font-tilt\">

    ";
        // line 15
        $cmsPartialParams = [];
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("site/navbar"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 16
        yield "
    <main class=\"flex-grow flex items-center justify-center container mx-auto px-4\">
      ";
        // line 18
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->pageFunction($context);
        // line 19
        yield "    </main>

    ";
        // line 21
        $cmsPartialParams = [];
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("site/footer"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 22
        yield "
    ";
        // line 23
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->assetsFunction('js');
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->displayBlock('scripts');
        // line 24
        yield "  </body>
</html>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/layouts/default.htm";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  94 => 24,  91 => 23,  88 => 22,  85 => 21,  81 => 19,  79 => 18,  75 => 16,  72 => 15,  63 => 10,  59 => 9,  54 => 8,  49 => 6,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"en\" class=\"tv dark\" data-theme=\"dataverse\">
  <head>
    <meta charset=\"utf-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <title>{{ this.page.title }} - The DataVerse</title>

    {% framework extras %}
    {% styles %}
    <link rel=\"stylesheet\" href=\"{{ 'assets/vendor/tailwind/tailwind.css'|theme }}\">
  </head>

  <body class=\"bg-dv-bg text-dv-text min-h-screen flex flex-col font-tilt\">

    {% partial 'site/navbar' %}

    <main class=\"flex-grow flex items-center justify-center container mx-auto px-4\">
      {% page %}
    </main>

    {% partial 'site/footer' %}

    {% scripts %}
  </body>
</html>", "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/layouts/default.htm", "");
    }
}
