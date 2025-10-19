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
    <link href=\"https://fonts.googleapis.com/css2?family=Tilt+Neon:wght@400;700&display=swap\" rel=\"stylesheet\">
    <title>";
        // line 7
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, ($context["this"] ?? null), "page", [], "any", false, false, false, 7), "title", [], "any", false, false, false, 7), "html", null, true);
        yield " - The DataVerse</title>

    ";
        // line 9
        $_minify = System\Classes\CombineAssets::instance()->useMinify;
        yield '<script src="' . Request::getBasePath() . '/modules/system/assets/js/framework-extras'.($_minify ? '.min' : '').'.js"></script>' . PHP_EOL;
        yield '<link rel="stylesheet" property="stylesheet" href="' . Request::getBasePath() .'/modules/system/assets/css/framework-extras.css">' . PHP_EOL;
        unset($_minify);
        // line 10
        yield "    ";
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->assetsFunction('css');
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->displayBlock('styles');
        // line 11
        yield "    <link rel=\"stylesheet\" href=\"";
        yield $this->extensions['Cms\Twig\Extension']->themeFilter("assets/vendor/tailwind/tailwind.css");
        yield "\">
  </head>

  <body class=\"bg-dv-bg text-dv-text min-h-screen flex flex-col\">

    ";
        // line 16
        $cmsPartialParams = [];
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("site/navbar"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 17
        yield "
    <main class=\"flex-grow flex items-center justify-center container mx-auto px-4\">
      ";
        // line 19
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->pageFunction($context);
        // line 20
        yield "    </main>

    ";
        // line 22
        $cmsPartialParams = [];
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("site/footer"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 23
        yield "
    ";
        // line 24
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->assetsFunction('js');
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->displayBlock('scripts');
        // line 25
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
        return array (  95 => 25,  92 => 24,  89 => 23,  86 => 22,  82 => 20,  80 => 19,  76 => 17,  73 => 16,  64 => 11,  60 => 10,  55 => 9,  50 => 7,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"en\" class=\"tv dark\" data-theme=\"dataverse\">
  <head>
    <meta charset=\"utf-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <link href=\"https://fonts.googleapis.com/css2?family=Tilt+Neon:wght@400;700&display=swap\" rel=\"stylesheet\">
    <title>{{ this.page.title }} - The DataVerse</title>

    {% framework extras %}
    {% styles %}
    <link rel=\"stylesheet\" href=\"{{ 'assets/vendor/tailwind/tailwind.css'|theme }}\">
  </head>

  <body class=\"bg-dv-bg text-dv-text min-h-screen flex flex-col\">

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
