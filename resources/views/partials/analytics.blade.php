@php
    $gaId = config('seo.ga_id');
    $gtmId = config('seo.gtm_id');
    $metaPixelId = config('seo.meta_pixel_id');
    $linkedInPartnerId = config('seo.linkedin_partner_id');
    $clarityId = config('seo.clarity_id');
    $googleAdsId = config('seo.google_ads_conversion_id');
    $googleAdsLabel = config('seo.google_ads_conversion_label');
@endphp

{{-- 1. Google Tag Manager (GTM) --}}
@if(!empty($gtmId))
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    <!-- End Google Tag Manager -->
@endif

{{-- 2. Google Analytics 4 (GA4) - Only load directly if GTM is NOT configured to avoid duplicate tracking --}}
@if(!empty($gaId) && empty($gtmId))
    <!-- Google Analytics 4 (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $gaId }}', {
        'anonymize_ip': true,
        'send_page_view': true
      });

      // Utility helper for GA4 Event Tracking
      window.trackGaEvent = function(eventName, params) {
        if (typeof gtag === 'function') {
          gtag('event', eventName, params || {});
        }
      };
    </script>
@endif

{{-- 3. Meta / Facebook Pixel --}}
@if(!empty($metaPixelId))
    <!-- Meta Pixel Code -->
    <script>
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '{{ $metaPixelId }}');
      fbq('track', 'PageView');

      window.trackMetaEvent = function(eventName, params) {
        if (typeof fbq === 'function') {
          fbq('track', eventName, params || {});
        }
      };
    </script>
@endif

{{-- 4. LinkedIn Insight Tag --}}
@if(!empty($linkedInPartnerId))
    <!-- LinkedIn Insight Tag -->
    <script type="text/javascript">
      _linkedin_partner_id = "{{ $linkedInPartnerId }}";
      window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
      window._linkedin_data_partner_ids.push(_linkedin_partner_id);
    </script>
    <script type="text/javascript">
      (function(l) {
      if (!l){window.lintrk = function(a,b){window.lintrk.q.push([a,b])};
      window.lintrk.q=[]}
      var s = document.getElementsByTagName("script")[0];
      var b = document.createElement("script");
      b.type = "text/javascript";b.async = true;
      b.src = "https://snap.licdn.com/li.lms-analytics/insight.min.js";
      s.parentNode.insertBefore(b, s);})(window.lintrk);
    </script>
@endif

{{-- 5. Microsoft Clarity --}}
@if(!empty($clarityId))
    <!-- Microsoft Clarity -->
    <script type="text/javascript">
      (function(c,l,a,r,i,t,y){
          c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
          t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
          y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
      })(window, document, "clarity", "script", "{{ $clarityId }}");
    </script>
@endif

{{-- 6. Google Ads Conversion Tracking --}}
@if(!empty($googleAdsId))
    <!-- Google Ads Conversion Tracking -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAdsId }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $googleAdsId }}');

      window.trackGoogleAdsConversion = function(value, currency) {
        if (typeof gtag === 'function') {
          gtag('event', 'conversion', {
              'send_to': '{{ $googleAdsId }}/{{ $googleAdsLabel ?? "" }}',
              'value': value || 0,
              'currency': currency || 'PKR'
          });
        }
      };
    </script>
@endif
