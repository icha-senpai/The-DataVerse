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

/* C:\laragon\www\active\octobertest2\themes/dataverse/pages/home.htm */
class __TwigTemplate_bf9cbf6f99710ff4c8b1ed8fb813bece extends Template
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
        yield "<section class=\"relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-[#080816] text-gray-200 pt-24\">

  <!-- Background grid + glow -->
  <div class=\"absolute inset-0 -z-10 bg-[url('/assets/images/neon-grid.svg')] bg-repeat opacity-20\"></div>
  <div class=\"absolute inset-0 -z-10 bg-gradient-to-b from-fuchsia-500/20 via-transparent to-cyan-400/20 blur-3xl\"></div>

  <!-- Hero -->
  <div class=\"max-w-4xl text-center px-6\">
    <h1 class=\"text-5xl md:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-500 via-cyan-400 to-fuchsia-500 drop-shadow-[0_0_30px_rgba(255,0,255,0.4)]\">
      Welcome to The DataVerse
    </h1>
    <p class=\"mt-6 text-lg text-gray-400\">
      A living archive of <span class=\"text-cyan-400 font-semibold\">Star Citizen</span>, <span class=\"text-fuchsia-400 font-semibold\">Minecraft</span>, <span class=\"text-purple-400 font-semibold\">osu!</span>, and all worlds that orbit IchaTV.
    </p>
    <a href=\"";
        // line 15
        yield $this->extensions['Cms\Twig\Extension']->pageFilter("twitch/index");
        yield "\" class=\"mt-10 inline-block px-8 py-4 rounded-full font-semibold text-[#080816] bg-gradient-to-r from-cyan-400 to-fuchsia-500 hover:opacity-90 transition\">
      Enter The Stream
    </a>
  </div>

  <!-- Floating cards -->
  <div class=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-24 max-w-6xl px-6\">
    ";
        // line 22
        $cmsPartialParams = [];
        $cmsPartialParams['title'] = "Star Citizen"        ;
        $cmsPartialParams['desc'] = "Commodity data, org logistics, and interstellar spreadsheets."        ;
        $cmsPartialParams['icon'] = "🚀"        ;
        $cmsPartialParams['link'] = "starcitizen/index"        ;
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("components/home-card"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 23
        yield "    ";
        $cmsPartialParams = [];
        $cmsPartialParams['title'] = "Minecraft"        ;
        $cmsPartialParams['desc'] = "Modpacks, server tools, and the world of Limitless 8."        ;
        $cmsPartialParams['icon'] = "🪓"        ;
        $cmsPartialParams['link'] = "minecraft/index"        ;
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("components/home-card"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 24
        yield "    ";
        $cmsPartialParams = [];
        $cmsPartialParams['title'] = "osu!"        ;
        $cmsPartialParams['desc'] = "Beats, clicks, and leaderboard stats in The DataVerse."        ;
        $cmsPartialParams['icon'] = "🎶"        ;
        $cmsPartialParams['link'] = "osu/index"        ;
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("components/home-card"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 25
        yield "    ";
        $cmsPartialParams = [];
        $cmsPartialParams['title'] = "Twitch"        ;
        $cmsPartialParams['desc'] = "Streaming schedule synced with Google Calendar."        ;
        $cmsPartialParams['icon'] = "📺"        ;
        $cmsPartialParams['link'] = "twitch/index"        ;
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("components/home-card"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 26
        yield "    ";
        $cmsPartialParams = [];
        $cmsPartialParams['title'] = "Books & Audio"        ;
        $cmsPartialParams['desc'] = "Libraries of sound and story — curated and chaotic."        ;
        $cmsPartialParams['icon'] = "📚"        ;
        $cmsPartialParams['link'] = "books/index"        ;
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("components/home-card"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 27
        yield "    ";
        $cmsPartialParams = [];
        $cmsPartialParams['title'] = "Wiki"        ;
        $cmsPartialParams['desc'] = "Lore, data, and development logs of The DataVerse project."        ;
        $cmsPartialParams['icon'] = "📖"        ;
        $cmsPartialParams['link'] = "wiki/index"        ;
        yield $this->env->getExtension(\Cms\Twig\Extension::class)->partialFunction("components/home-card"        , array_merge($context, ['__cms_partial_params' => $cmsPartialParams], $cmsPartialParams)        , true);
        // line 28
        yield "  </div>

  <!-- Footer glow -->
  <div class=\"absolute bottom-0 w-full h-[200px] bg-gradient-to-t from-[#0ff3] via-transparent to-transparent blur-3xl\"></div>
</section>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/pages/home.htm";
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
        return array (  115 => 28,  107 => 27,  99 => 26,  91 => 25,  83 => 24,  75 => 23,  68 => 22,  58 => 15,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<section class=\"relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-[#080816] text-gray-200 pt-24\">

  <!-- Background grid + glow -->
  <div class=\"absolute inset-0 -z-10 bg-[url('/assets/images/neon-grid.svg')] bg-repeat opacity-20\"></div>
  <div class=\"absolute inset-0 -z-10 bg-gradient-to-b from-fuchsia-500/20 via-transparent to-cyan-400/20 blur-3xl\"></div>

  <!-- Hero -->
  <div class=\"max-w-4xl text-center px-6\">
    <h1 class=\"text-5xl md:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-500 via-cyan-400 to-fuchsia-500 drop-shadow-[0_0_30px_rgba(255,0,255,0.4)]\">
      Welcome to The DataVerse
    </h1>
    <p class=\"mt-6 text-lg text-gray-400\">
      A living archive of <span class=\"text-cyan-400 font-semibold\">Star Citizen</span>, <span class=\"text-fuchsia-400 font-semibold\">Minecraft</span>, <span class=\"text-purple-400 font-semibold\">osu!</span>, and all worlds that orbit IchaTV.
    </p>
    <a href=\"{{ 'twitch/index'|page }}\" class=\"mt-10 inline-block px-8 py-4 rounded-full font-semibold text-[#080816] bg-gradient-to-r from-cyan-400 to-fuchsia-500 hover:opacity-90 transition\">
      Enter The Stream
    </a>
  </div>

  <!-- Floating cards -->
  <div class=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-24 max-w-6xl px-6\">
    {% partial 'components/home-card' title='Star Citizen' desc='Commodity data, org logistics, and interstellar spreadsheets.' icon='🚀' link='starcitizen/index' %}
    {% partial 'components/home-card' title='Minecraft' desc='Modpacks, server tools, and the world of Limitless 8.' icon='🪓' link='minecraft/index' %}
    {% partial 'components/home-card' title='osu!' desc='Beats, clicks, and leaderboard stats in The DataVerse.' icon='🎶' link='osu/index' %}
    {% partial 'components/home-card' title='Twitch' desc='Streaming schedule synced with Google Calendar.' icon='📺' link='twitch/index' %}
    {% partial 'components/home-card' title='Books & Audio' desc='Libraries of sound and story — curated and chaotic.' icon='📚' link='books/index' %}
    {% partial 'components/home-card' title='Wiki' desc='Lore, data, and development logs of The DataVerse project.' icon='📖' link='wiki/index' %}
  </div>

  <!-- Footer glow -->
  <div class=\"absolute bottom-0 w-full h-[200px] bg-gradient-to-t from-[#0ff3] via-transparent to-transparent blur-3xl\"></div>
</section>", "C:\\laragon\\www\\active\\octobertest2\\themes/dataverse/pages/home.htm", "");
    }
}
