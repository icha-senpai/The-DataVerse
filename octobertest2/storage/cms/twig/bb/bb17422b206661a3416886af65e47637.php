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

/* C:\laragon\www\active\octobertest2\themes/dataverse/partials/components/home-card.htm */
class __TwigTemplate_0bffe7474a3a240140ad00ebaa47188d extends Template
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
        yield "<div class=\"group relative bg-[#0d0d1a]/80 border border-[#1f1f30] rounded-2xl p-6 backdrop-blur-md hover:border-fuchsia-500/50 transition\">
  <div class=\"text-4xl mb-4\">";
        // line 2
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["icon"] ?? null), "html", null, true);
        yield "</div>
  <h2 class=\"text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-400 to-cyan-400\">";
        // line 3
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["title"] ?? null), "html", null, true);
        yield "</h2>
  <p class=\"mt-2 text-gray-400 text-sm\">";
        // line 4
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["desc"] ?? null), "html", null, true);
        yield "</p>
  <a href=\"";
        // line 5
        yield $this->extensions['Cms\Twig\Extension']->pageFilter(($context["link"] ?? null));
        yield "\" class=\"mt-6 inline-block text-sm font-semibold text-cyan-400 hover:text-fuchsia-400 transition\">
    Explore →
  </a>
  <div class=\"absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-20 bg-gradient-to-r from-fuchsia-500 to-cyan-400 blur-2xl transition\"></div>
</div>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/partials/components/home-card.htm";
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
        return array (  57 => 5,  53 => 4,  49 => 3,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<div class=\"group relative bg-[#0d0d1a]/80 border border-[#1f1f30] rounded-2xl p-6 backdrop-blur-md hover:border-fuchsia-500/50 transition\">
  <div class=\"text-4xl mb-4\">{{ icon }}</div>
  <h2 class=\"text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-400 to-cyan-400\">{{ title }}</h2>
  <p class=\"mt-2 text-gray-400 text-sm\">{{ desc }}</p>
  <a href=\"{{ link|page }}\" class=\"mt-6 inline-block text-sm font-semibold text-cyan-400 hover:text-fuchsia-400 transition\">
    Explore →
  </a>
  <div class=\"absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-20 bg-gradient-to-r from-fuchsia-500 to-cyan-400 blur-2xl transition\"></div>
</div>", "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/partials/components/home-card.htm", "");
    }
}
