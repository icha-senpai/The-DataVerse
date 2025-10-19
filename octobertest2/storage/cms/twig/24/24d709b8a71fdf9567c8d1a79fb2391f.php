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

/* C:\laragon\www\active\octobertest2\themes/dataverse/pages/wiki/wiki.htm */
class __TwigTemplate_0faebcc9129114ddcd8c977942eee0f6 extends Template
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
        $context["entries"] = Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, ($context["tailor"] ?? null), "entries", ["Wiki\\Entry"], "method", false, false, false, 1), "paginate", [10], "method", false, false, false, 1);
        // line 2
        $context["categories"] = Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, ($context["tailor"] ?? null), "entries", ["Wiki\\Category"], "method", false, false, false, 2);
        // line 3
        $context["tags"] = Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, ($context["tailor"] ?? null), "entries", ["Wiki\\Tag"], "method", false, false, false, 3);
        // line 4
        yield "
<div class=\"max-w-5xl mx-auto text-gray-200 py-12\">
  <h1 class=\"text-4xl font-orbitron mb-6 text-dv-accent\">The DataVerse Wiki</h1>

  <div class=\"grid grid-cols-3 gap-4 mb-10\">
    <div>
      <h2 class=\"text-xl text-dv-accent2 mb-2\">Categories</h2>
      <ul>
        ";
        // line 12
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["categories"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["cat"]) {
            // line 13
            yield "          <li><a href=\"/wiki/category/";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, $context["cat"], "slug", [], "any", false, false, false, 13), "html", null, true);
            yield "\" class=\"hover:text-dv-accent2\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, $context["cat"], "name", [], "any", false, false, false, 13), "html", null, true);
            yield "</a></li>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['cat'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 15
        yield "      </ul>
    </div>

    <div>
      <h2 class=\"text-xl text-dv-accent2 mb-2\">Tags</h2>
      <ul class=\"flex flex-wrap gap-2\">
        ";
        // line 21
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["tags"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["tag"]) {
            // line 22
            yield "          <a href=\"?tag=";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, $context["tag"], "slug", [], "any", false, false, false, 22), "html", null, true);
            yield "\" class=\"px-2 py-1 bg-dv-surface/50 border border-dv-accent rounded\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, $context["tag"], "name", [], "any", false, false, false, 22), "html", null, true);
            yield "</a>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['tag'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 24
        yield "      </ul>
    </div>
  </div>

  <div class=\"space-y-6\">
    ";
        // line 29
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["entries"] ?? null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["entry"]) {
            // line 30
            yield "      <article class=\"bg-dv-surface/60 p-6 rounded-2xl shadow-md\">
        <a href=\"/wiki/";
            // line 31
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, $context["entry"], "slug", [], "any", false, false, false, 31), "html", null, true);
            yield "\" class=\"text-2xl text-dv-accent hover:text-dv-accent2\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, $context["entry"], "title", [], "any", false, false, false, 31), "html", null, true);
            yield "</a>
        <p class=\"text-gray-400 text-sm mb-3\">";
            // line 32
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, $context["entry"], "category", [], "any", false, false, false, 32), "name", [], "any", false, false, false, 32), "html", null, true);
            yield "</p>
        <p class=\"text-gray-300\">";
            // line 33
            yield Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, $context["entry"], "summary", [], "any", false, false, false, 33);
            yield "</p>
      </article>
    ";
            $context['_iterated'] = true;
        }
        // line 35
        if (!$context['_iterated']) {
            // line 36
            yield "      <p>No entries found.</p>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['entry'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 38
        yield "  </div>

  <div class=\"mt-8\">
    ";
        // line 41
        yield Cms\Twig\GetAttrNode::customGetAttribute($this->env, $this->source, ($context["entries"] ?? null), "render", [], "any", false, false, false, 41);
        yield "
  </div>
</div>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/pages/wiki/wiki.htm";
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
        return array (  142 => 41,  137 => 38,  130 => 36,  128 => 35,  121 => 33,  117 => 32,  111 => 31,  108 => 30,  103 => 29,  96 => 24,  85 => 22,  81 => 21,  73 => 15,  62 => 13,  58 => 12,  48 => 4,  46 => 3,  44 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% set entries = tailor.entries('Wiki\\\\Entry').paginate(10) %}
{% set categories = tailor.entries('Wiki\\\\Category') %}
{% set tags = tailor.entries('Wiki\\\\Tag') %}

<div class=\"max-w-5xl mx-auto text-gray-200 py-12\">
  <h1 class=\"text-4xl font-orbitron mb-6 text-dv-accent\">The DataVerse Wiki</h1>

  <div class=\"grid grid-cols-3 gap-4 mb-10\">
    <div>
      <h2 class=\"text-xl text-dv-accent2 mb-2\">Categories</h2>
      <ul>
        {% for cat in categories %}
          <li><a href=\"/wiki/category/{{ cat.slug }}\" class=\"hover:text-dv-accent2\">{{ cat.name }}</a></li>
        {% endfor %}
      </ul>
    </div>

    <div>
      <h2 class=\"text-xl text-dv-accent2 mb-2\">Tags</h2>
      <ul class=\"flex flex-wrap gap-2\">
        {% for tag in tags %}
          <a href=\"?tag={{ tag.slug }}\" class=\"px-2 py-1 bg-dv-surface/50 border border-dv-accent rounded\">{{ tag.name }}</a>
        {% endfor %}
      </ul>
    </div>
  </div>

  <div class=\"space-y-6\">
    {% for entry in entries %}
      <article class=\"bg-dv-surface/60 p-6 rounded-2xl shadow-md\">
        <a href=\"/wiki/{{ entry.slug }}\" class=\"text-2xl text-dv-accent hover:text-dv-accent2\">{{ entry.title }}</a>
        <p class=\"text-gray-400 text-sm mb-3\">{{ entry.category.name }}</p>
        <p class=\"text-gray-300\">{{ entry.summary|raw }}</p>
      </article>
    {% else %}
      <p>No entries found.</p>
    {% endfor %}
  </div>

  <div class=\"mt-8\">
    {{ entries.render|raw }}
  </div>
</div>", "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/pages/wiki/wiki.htm", "");
    }
}
