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

/* C:\laragon\www\active\octobertest2\themes/dataverse/partials/site/navbar.htm */
class __TwigTemplate_ad8fb191461b8736dc1f08ed1ca34e84 extends Template
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
        yield "<nav class=\"bg-dv-surface/80 backdrop-blur-md border-b border-gray-800 shadow-neon fixed w-full z-50\">
  <div class=\"max-w-7xl mx-auto px-6\">
    <div class=\"flex justify-between items-center h-16\">
      <!-- Logo / Brand -->
      <a href=\"";
        // line 5
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("home");
        yield "\" class=\"text-xl font-bold text-dv-accent hover:text-dv-accent2 font-tilt\">
        The DataVerse
      </a>

      <!-- Desktop Menu -->
      <ul class=\"hidden md:flex space-x-8 font-tilt text-dv-text\">
        <li>
          <a href=\"";
        // line 12
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("home");
        yield "\" class=\"hover:text-dv-accent2 transition\">Home</a>
        </li>

        <!-- Cargo Dropdown -->
        <li class=\"relative group\">
          <button class=\"hover:text-dv-accent2 flex items-center transition\">
            Cargo
            <svg class=\"w-4 h-4 ml-1\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
              <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 9l-7 7-7-7\" />
            </svg>
          </button>
          <ul
            class=\"absolute left-0 mt-2 bg-dv-surface border border-gray-700 rounded-lg shadow-lg opacity-0 invisible 
                   group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out min-w-[180px] 
                   pt-2 pb-2 z-40\">
            <li><a href=\"";
        // line 27
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("cargo/index");
        yield "\" class=\"block px-4 py-2 hover:bg-gray-800\">Overview</a></li>
            <li><a href=\"";
        // line 28
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("cargo/table");
        yield "\" class=\"block px-4 py-2 hover:bg-gray-800\">Commodity Table</a></li>
          </ul>
        </li>

        <!-- Minecraft Dropdown -->
        <li class=\"relative group\">
          <button class=\"hover:text-dv-accent2 flex items-center transition\">
            Minecraft
            <svg class=\"w-4 h-4 ml-1\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
              <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 9l-7 7-7-7\" />
            </svg>
          </button>
          <ul
            class=\"absolute left-0 mt-2 bg-dv-surface border border-gray-700 rounded-lg shadow-lg opacity-0 invisible 
                   group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out min-w-[180px] 
                   pt-2 pb-2 z-40\">
            <li><a href=\"";
        // line 44
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("minecraft/index");
        yield "\" class=\"block px-4 py-2 hover:bg-gray-800\">Overview</a></li>
            <li><a href=\"";
        // line 45
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("minecraft/changelog");
        yield "\" class=\"block px-4 py-2 hover:bg-gray-800\">Changelog</a></li>
          </ul>
        </li>

        <!-- Regular Links -->
        <li><a href=\"";
        // line 50
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("starcitizen/index");
        yield "\" class=\"hover:text-dv-accent2 transition\">Star Citizen</a></li>
        <li><a href=\"";
        // line 51
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("twitch/index");
        yield "\" class=\"hover:text-dv-accent2 transition\">Twitch</a></li>
        <li><a href=\"";
        // line 52
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("wiki/wiki");
        yield "\" class=\"hover:text-dv-accent2 transition\">Wiki</a></li>
      </ul>

      <!-- Mobile Menu Toggle -->
      <button id=\"mobile-toggle\" class=\"md:hidden focus:outline-none\">
        <svg class=\"w-6 h-6 text-dv-accent\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
          <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\"
            d=\"M4 6h16M4 12h16M4 18h16\" />
        </svg>
      </button>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id=\"mobile-menu\" class=\"hidden flex-col space-y-2 mt-4 md:hidden font-tilt text-dv-text\">
      <a href=\"";
        // line 66
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("home");
        yield "\" class=\"hover:text-dv-accent2\">Home</a>
      <a href=\"";
        // line 67
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("cargo/index");
        yield "\" class=\"hover:text-dv-accent2\">Cargo</a>
      <a href=\"";
        // line 68
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("minecraft/index");
        yield "\" class=\"hover:text-dv-accent2\">Minecraft</a>
      <a href=\"";
        // line 69
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("starrcitizen/index");
        yield "\" class=\"hover:text-dv-accent2\">Star Citizen</a>
      <a href=\"";
        // line 70
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("twitch/index");
        yield "\" class=\"hover:text-dv-accent2\">Twitch</a>
      <a href=\"";
        // line 71
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("wiki/wiki");
        yield "\" class=\"hover:text-dv-accent2\">Wiki</a>
    </div>
  </div>
</nav>

<script>
  // Mobile toggle script
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('mobile-toggle');
    const menu = document.getElementById('mobile-menu');
    toggle.addEventListener('click', () => {
      menu.classList.toggle('hidden');
      menu.classList.toggle('flex');
    });
  });
</script>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/partials/site/navbar.htm";
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
        return array (  156 => 71,  152 => 70,  148 => 69,  144 => 68,  140 => 67,  136 => 66,  119 => 52,  115 => 51,  111 => 50,  103 => 45,  99 => 44,  80 => 28,  76 => 27,  58 => 12,  48 => 5,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<nav class=\"bg-dv-surface/80 backdrop-blur-md border-b border-gray-800 shadow-neon fixed w-full z-50\">
  <div class=\"max-w-7xl mx-auto px-6\">
    <div class=\"flex justify-between items-center h-16\">
      <!-- Logo / Brand -->
      <a href=\"{{ 'home'|page }}\" class=\"text-xl font-bold text-dv-accent hover:text-dv-accent2 font-tilt\">
        The DataVerse
      </a>

      <!-- Desktop Menu -->
      <ul class=\"hidden md:flex space-x-8 font-tilt text-dv-text\">
        <li>
          <a href=\"{{ 'home'|page }}\" class=\"hover:text-dv-accent2 transition\">Home</a>
        </li>

        <!-- Cargo Dropdown -->
        <li class=\"relative group\">
          <button class=\"hover:text-dv-accent2 flex items-center transition\">
            Cargo
            <svg class=\"w-4 h-4 ml-1\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
              <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 9l-7 7-7-7\" />
            </svg>
          </button>
          <ul
            class=\"absolute left-0 mt-2 bg-dv-surface border border-gray-700 rounded-lg shadow-lg opacity-0 invisible 
                   group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out min-w-[180px] 
                   pt-2 pb-2 z-40\">
            <li><a href=\"{{ 'cargo/index'|page }}\" class=\"block px-4 py-2 hover:bg-gray-800\">Overview</a></li>
            <li><a href=\"{{ 'cargo/table'|page }}\" class=\"block px-4 py-2 hover:bg-gray-800\">Commodity Table</a></li>
          </ul>
        </li>

        <!-- Minecraft Dropdown -->
        <li class=\"relative group\">
          <button class=\"hover:text-dv-accent2 flex items-center transition\">
            Minecraft
            <svg class=\"w-4 h-4 ml-1\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
              <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 9l-7 7-7-7\" />
            </svg>
          </button>
          <ul
            class=\"absolute left-0 mt-2 bg-dv-surface border border-gray-700 rounded-lg shadow-lg opacity-0 invisible 
                   group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out min-w-[180px] 
                   pt-2 pb-2 z-40\">
            <li><a href=\"{{ 'minecraft/index'|page }}\" class=\"block px-4 py-2 hover:bg-gray-800\">Overview</a></li>
            <li><a href=\"{{ 'minecraft/changelog'|page }}\" class=\"block px-4 py-2 hover:bg-gray-800\">Changelog</a></li>
          </ul>
        </li>

        <!-- Regular Links -->
        <li><a href=\"{{ 'starcitizen/index'|page }}\" class=\"hover:text-dv-accent2 transition\">Star Citizen</a></li>
        <li><a href=\"{{ 'twitch/index'|page }}\" class=\"hover:text-dv-accent2 transition\">Twitch</a></li>
        <li><a href=\"{{ 'wiki/wiki'|page }}\" class=\"hover:text-dv-accent2 transition\">Wiki</a></li>
      </ul>

      <!-- Mobile Menu Toggle -->
      <button id=\"mobile-toggle\" class=\"md:hidden focus:outline-none\">
        <svg class=\"w-6 h-6 text-dv-accent\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
          <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\"
            d=\"M4 6h16M4 12h16M4 18h16\" />
        </svg>
      </button>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id=\"mobile-menu\" class=\"hidden flex-col space-y-2 mt-4 md:hidden font-tilt text-dv-text\">
      <a href=\"{{ 'home'|page }}\" class=\"hover:text-dv-accent2\">Home</a>
      <a href=\"{{ 'cargo/index'|page }}\" class=\"hover:text-dv-accent2\">Cargo</a>
      <a href=\"{{ 'minecraft/index'|page }}\" class=\"hover:text-dv-accent2\">Minecraft</a>
      <a href=\"{{ 'starrcitizen/index'|page }}\" class=\"hover:text-dv-accent2\">Star Citizen</a>
      <a href=\"{{ 'twitch/index'|page }}\" class=\"hover:text-dv-accent2\">Twitch</a>
      <a href=\"{{ 'wiki/wiki'|page }}\" class=\"hover:text-dv-accent2\">Wiki</a>
    </div>
  </div>
</nav>

<script>
  // Mobile toggle script
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('mobile-toggle');
    const menu = document.getElementById('mobile-menu');
    toggle.addEventListener('click', () => {
      menu.classList.toggle('hidden');
      menu.classList.toggle('flex');
    });
  });
</script>", "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/partials/site/navbar.htm", "");
    }
}
