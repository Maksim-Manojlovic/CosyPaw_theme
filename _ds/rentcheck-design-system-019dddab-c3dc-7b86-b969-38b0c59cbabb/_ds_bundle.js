/* @ds-bundle: {"format":3,"namespace":"RentCheckDesignSystem_019ddd","components":[],"sourceHashes":{"add-property/app.jsx":"e05fe1dea728","add-property/controls.jsx":"d91272091407","add-property/icons.jsx":"ba82380acb2c","add-property/photo-upload.jsx":"34708b379dd1","add-property/tweaks-panel.jsx":"6591467622ed","assets/filters.js":"9533bae56e31","assets/results.js":"e6f483e0343b","assets/search-system.js":"257ecb903998","design-canvas.jsx":"5d0e39003628","ui_kits/rentcheck/Components.jsx":"50adc27d4912","ui_kits/rentcheck/LandingScreen.jsx":"fdd225d63db0","ui_kits/rentcheck/MarketplaceScreen.jsx":"4dea373e7ce0","ui_kits/rentcheck/PassportScreen.jsx":"170bada928ba","ui_kits/rentcheck/PropertyScreen.jsx":"d77a50d3bddf","ui_kits/rentcheck/Tokens.jsx":"3178ee10ca24"},"inlinedExternals":[],"unexposedExports":[]} */

(() => {

const __ds_ns = (window.RentCheckDesignSystem_019ddd = window.RentCheckDesignSystem_019ddd || {});

const __ds_scope = {};

(__ds_ns.__errors = __ds_ns.__errors || []);

// add-property/app.jsx
try { (() => {
// RentCheck — Dodaj nekretninu (Add Property) — main app

const {
  useState,
  useRef,
  useEffect,
  useCallback
} = React;

// ── Option data ──────────────────────────────────────────────────────────────
const TIP_OPTS = [{
  value: 'stan',
  label: 'Stan',
  icon: 'home'
}, {
  value: 'kuca',
  label: 'Kuća',
  icon: 'house'
}, {
  value: 'soba',
  label: 'Soba',
  icon: 'bed'
}, {
  value: 'poslovni',
  label: 'Poslovni prostor',
  icon: 'briefcase'
}, {
  value: 'lokal',
  label: 'Lokal',
  icon: 'store'
}, {
  value: 'splav',
  label: 'Splav',
  icon: 'ship'
}, {
  value: 'vikendica',
  label: 'Vikendica',
  icon: 'tent'
}];
const STRUKTURA_OPTS = [{
  value: 'garsonjera',
  label: 'Garsonjera',
  sub: '0.0'
}, {
  value: 'jednosoban',
  label: 'Jednosoban',
  sub: '1.0'
}, {
  value: 'jednoiposoban',
  label: 'Jednoiposoban',
  sub: '1.5'
}, {
  value: 'dvosoban',
  label: 'Dvosoban',
  sub: '2.0'
}, {
  value: 'dvoiposoban',
  label: 'Dvoiposoban',
  sub: '2.5'
}, {
  value: 'trosoban',
  label: 'Trosoban',
  sub: '3.0'
}, {
  value: 'troiposoban',
  label: 'Troiposoban',
  sub: '3.5'
}, {
  value: 'cetvorosoban',
  label: 'Četvorosoban',
  sub: '4.0'
}, {
  value: 'cetvoroiposoban',
  label: 'Četvoroiposoban',
  sub: '4.5'
}, {
  value: 'petosoban',
  label: 'Petosoban+',
  sub: '5.0+'
}];
const OPSTINA_OPTS = ['Stari grad', 'Vračar', 'Savski venac', 'Novi Beograd', 'Zvezdara', 'Voždovac', 'Palilula', 'Zemun', 'Čukarica', 'Rakovica', 'Zvezdara', 'Mirijevo'].filter((v, i, a) => a.indexOf(v) === i).map(m => ({
  value: m,
  label: m,
  icon: 'map-pin'
}));
const NAMESTENOST = [{
  value: 'namesten',
  label: 'Namešten'
}, {
  value: 'polu',
  label: 'Polunamešten'
}, {
  value: 'prazan',
  label: 'Prazan'
}];
const STOLARIJA = [{
  value: 'pvc',
  label: 'PVC'
}, {
  value: 'alu',
  label: 'Aluminijum'
}, {
  value: 'drvo',
  label: 'Drvo'
}, {
  value: 'kombo',
  label: 'Kombinovano'
}];
const GREJANJE = [{
  value: 'cg',
  label: 'Centralno (CG)',
  icon: 'flame'
}, {
  value: 'etazno',
  label: 'Etažno',
  icon: 'flame'
}, {
  value: 'ta',
  label: 'TA peć',
  icon: 'zap'
}, {
  value: 'podno',
  label: 'Podno',
  icon: 'flame'
}, {
  value: 'klima',
  label: 'Klima / inverter',
  icon: 'wind'
}, {
  value: 'gas',
  label: 'Gas',
  icon: 'flame'
}];
const PARKING = [{
  value: 'bez',
  label: 'Bez zone',
  icon: 'car'
}, {
  value: 'zona1',
  label: 'Zona 1 (crvena)',
  icon: 'car'
}, {
  value: 'zona2',
  label: 'Zona 2 (žuta)',
  icon: 'car'
}, {
  value: 'zona3',
  label: 'Zona 3 (zelena)',
  icon: 'car'
}, {
  value: 'privatni',
  label: 'Privatni parking',
  icon: 'car'
}, {
  value: 'garaza',
  label: 'Garaža',
  icon: 'car'
}];
const PERIOD = [{
  value: 'mesecno',
  label: 'Mesečno'
}, {
  value: 'tromesecno',
  label: 'Tromesečno'
}, {
  value: 'polugod',
  label: 'Polugodišnje'
}, {
  value: 'godisnje',
  label: 'Godišnje'
}];
const MIN_ZAKUP = [{
  value: '6',
  label: '6 meseci'
}, {
  value: '12',
  label: '12 meseci'
}, {
  value: '24',
  label: '24 meseca'
}, {
  value: 'bez',
  label: 'Bez minimuma'
}];
const LJUBIMCI = [{
  value: 'da',
  label: 'Dozvoljeni'
}, {
  value: 'dogovor',
  label: 'Uz dogovor'
}, {
  value: 'ne',
  label: 'Nisu'
}];
const DODATNI_PROSTORI = [{
  id: 'terasa',
  label: 'Terasa',
  icon: 'maximize'
}, {
  id: 'lodja',
  label: 'Lođa',
  icon: 'maximize'
}, {
  id: 'balkon',
  label: 'Balkon',
  icon: 'maximize'
}, {
  id: 'ostava',
  label: 'Ostava',
  icon: 'package'
}, {
  id: 'podrum',
  label: 'Podrum',
  icon: 'package'
}, {
  id: 'tavan',
  label: 'Tavan',
  icon: 'package'
}, {
  id: 'garaza',
  label: 'Garaža',
  icon: 'car'
}];
const OPREMA_NEKRETNINE = [{
  id: 'klima',
  label: 'Klima',
  icon: 'wind'
}, {
  id: 'ves',
  label: 'Veš mašina',
  icon: 'droplets'
}, {
  id: 'sudo',
  label: 'Sudo-mašina',
  icon: 'droplets'
}, {
  id: 'frizider',
  label: 'Frižider',
  icon: 'package'
}, {
  id: 'sporet',
  label: 'Šporet',
  icon: 'flame'
}, {
  id: 'bojler',
  label: 'Bojler',
  icon: 'thermometer'
}, {
  id: 'internet',
  label: 'Internet',
  icon: 'wifi'
}, {
  id: 'kablovska',
  label: 'Kablovska TV',
  icon: 'tv'
}, {
  id: 'tv',
  label: 'Smart TV',
  icon: 'tv'
}, {
  id: 'posudje',
  label: 'Posuđe',
  icon: 'utensils'
}];
const OPREMA_OBJEKTA = [{
  id: 'lift',
  label: 'Lift',
  icon: 'arrow-up-down'
}, {
  id: 'interfon',
  label: 'Interfon',
  icon: 'phone'
}, {
  id: 'nadzor',
  label: 'Video nadzor',
  icon: 'eye'
}, {
  id: 'garaza',
  label: 'Garaža',
  icon: 'car'
}, {
  id: 'parking',
  label: 'Parking',
  icon: 'car'
}, {
  id: 'podrum',
  label: 'Podrum',
  icon: 'package'
}, {
  id: 'recepcija',
  label: 'Recepcija',
  icon: 'users'
}, {
  id: 'invalidi',
  label: 'Pristup invalidima',
  icon: 'shield'
}];
const STEPS = [{
  id: 'sec-osnovni',
  icon: 'home',
  name: 'Osnovni podaci',
  meta: 'Tip · cena · adresa'
}, {
  id: 'sec-info',
  icon: 'list',
  name: 'Osnovne informacije',
  meta: 'Površina · sobe'
}, {
  id: 'sec-uslovi',
  icon: 'calendar',
  name: 'Uslovi zakupa',
  meta: 'Depozit · period'
}, {
  id: 'sec-troskovi',
  icon: 'euro',
  name: 'Troškovi',
  meta: 'Režije'
}, {
  id: 'sec-oprema',
  icon: 'sparkles',
  name: 'Opremljenost',
  meta: 'Tagovi'
}, {
  id: 'sec-foto',
  icon: 'camera',
  name: 'Fotografije',
  meta: 'Vođeno'
}];

// ── Local number stepper ─────────────────────────────────────────────────────
function NumStepper({
  value,
  onChange,
  min = 0,
  max = 20
}) {
  const v = Number(value) || 0;
  const set = n => onChange(String(Math.max(min, Math.min(max, n))));
  const btn = {
    width: 38,
    height: 42,
    display: 'grid',
    placeItems: 'center',
    border: 'none',
    background: 'rgba(26,45,71,0.55)',
    color: 'var(--color-ink-secondary)',
    transition: 'background 120ms, color 120ms'
  };
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      border: '0.5px solid var(--color-border-default)',
      borderRadius: 'var(--field-radius)',
      overflow: 'hidden',
      background: 'rgba(26,45,71,0.55)',
      width: 'fit-content'
    }
  }, /*#__PURE__*/React.createElement("button", {
    type: "button",
    style: btn,
    onClick: () => set(v - 1),
    onMouseEnter: e => e.currentTarget.style.color = 'var(--accent-tint)',
    onMouseLeave: e => e.currentTarget.style.color = 'var(--color-ink-secondary)'
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "chevron-left",
    size: 16
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      minWidth: 48,
      textAlign: 'center',
      fontFamily: 'var(--font-mono)',
      fontSize: 15,
      color: 'var(--color-ink-primary)'
    }
  }, v), /*#__PURE__*/React.createElement("button", {
    type: "button",
    style: btn,
    onClick: () => set(v + 1),
    onMouseEnter: e => e.currentTarget.style.color = 'var(--accent-tint)',
    onMouseLeave: e => e.currentTarget.style.color = 'var(--color-ink-secondary)'
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "chevron-right",
    size: 16
  })));
}

// ── Tweaks ────────────────────────────────────────────────────────────────────
const ACCENTS = {
  '#1D9E75': {
    a: '#1D9E75',
    strong: '#0F6E56',
    tint: '#9FE1CB',
    soft: 'rgba(29,158,117,0.08)',
    line: 'rgba(29,158,117,0.30)',
    glow: 'rgba(29,158,117,0.10)'
  },
  '#378ADD': {
    a: '#378ADD',
    strong: '#185FA5',
    tint: '#B5D4F4',
    soft: 'rgba(55,138,221,0.10)',
    line: 'rgba(55,138,221,0.32)',
    glow: 'rgba(55,138,221,0.12)'
  },
  '#1FB6C9': {
    a: '#1FB6C9',
    strong: '#0E7A88',
    tint: '#9DE7EF',
    soft: 'rgba(31,182,201,0.10)',
    line: 'rgba(31,182,201,0.30)',
    glow: 'rgba(31,182,201,0.12)'
  },
  '#7A5AE0': {
    a: '#7A5AE0',
    strong: '#4E36A8',
    tint: '#C9BCF5',
    soft: 'rgba(122,90,224,0.12)',
    line: 'rgba(122,90,224,0.32)',
    glow: 'rgba(122,90,224,0.12)'
  }
};
const RADIUS_MAP = {
  Oštro: '6px',
  Meko: '10px',
  Zaobljeno: '15px'
};
const TWEAK_DEFAULTS = {
  accent: '#1D9E75',
  glass: 16,
  radius: 'Meko'
};

// ── Main App ────────────────────────────────────────────────────────────────
const DEFAULT_FORM = {
  tip: 'stan',
  struktura: 'dvosoban',
  naslov: '',
  adresa: '',
  opstina: 'Vračar',
  cena: '',
  opis: '',
  povrsina: '',
  sprat: '',
  ukupnoSpratova: '',
  godina: '',
  namestenost: 'namesten',
  stolarija: 'pvc',
  visina: '',
  spavace: '1',
  kupatila: '1',
  grejanje: 'cg',
  parking: 'zona2',
  depozit: '',
  minZakup: '12',
  period: 'mesecno',
  useljivo: '',
  ljubimci: 'dogovor',
  infostan: '',
  struja: '',
  internet: ''
};
function App() {
  const [t, setTweak] = useTweaks(TWEAK_DEFAULTS);
  const [form, setForm] = useState(() => {
    try {
      const s = localStorage.getItem('rc_addprop');
      return s ? {
        ...DEFAULT_FORM,
        ...JSON.parse(s)
      } : DEFAULT_FORM;
    } catch (e) {
      return DEFAULT_FORM;
    }
  });
  const [dodatni, setDodatni] = useState({});
  const [oprema, setOprema] = useState({});
  const [photos, setPhotos] = useState({});
  const [open, setOpen] = useState({
    'sec-osnovni': true,
    'sec-info': true,
    'sec-uslovi': true,
    'sec-troskovi': true,
    'sec-oprema': true,
    'sec-foto': true
  });
  const [activeStep, setActiveStep] = useState('sec-osnovni');
  const [navOpen, setNavOpen] = useState(false);
  const scrollRef = useRef(null);
  const set = (k, v) => setForm(f => ({
    ...f,
    [k]: v
  }));
  const toggle = (obj, setObj, id) => setObj(o => ({
    ...o,
    [id]: !o[id]
  }));
  useEffect(() => {
    try {
      localStorage.setItem('rc_addprop', JSON.stringify(form));
    } catch (e) {}
  }, [form]);

  // apply tweaks to CSS variables
  useEffect(() => {
    const r = document.documentElement.style;
    const p = ACCENTS[t.accent] || ACCENTS['#1D9E75'];
    r.setProperty('--accent', p.a);
    r.setProperty('--accent-strong', p.strong);
    r.setProperty('--accent-tint', p.tint);
    r.setProperty('--accent-soft', p.soft);
    r.setProperty('--accent-line', p.line);
    r.setProperty('--accent-glow', p.glow);
    r.setProperty('--glass-blur', (t.glass ?? 16) + 'px');
    r.setProperty('--field-radius', RADIUS_MAP[t.radius] || '10px');
  }, [t.accent, t.glass, t.radius]);
  const plan = buildPhotoPlan(form.tip, form.struktura);
  const counts = planCounts(plan, photos);
  const onUpload = useCallback((id, src) => setPhotos(p => ({
    ...p,
    [id]: src
  })), []);
  const onRemove = useCallback(id => setPhotos(p => {
    const n = {
      ...p
    };
    delete n[id];
    return n;
  }), []);
  const scrollTo = id => {
    const el = document.getElementById(id);
    const area = scrollRef.current;
    if (el && area) {
      setOpen(o => ({
        ...o,
        [id]: true
      }));
      requestAnimationFrame(() => area.scrollTo({
        top: el.offsetTop - 18,
        behavior: 'smooth'
      }));
    }
    setNavOpen(false);
  };

  // active step on scroll
  const onScroll = () => {
    const area = scrollRef.current;
    if (!area) return;
    let cur = STEPS[0].id;
    for (const s of STEPS) {
      const el = document.getElementById(s.id);
      if (el && el.offsetTop - 80 <= area.scrollTop) cur = s.id;
    }
    setActiveStep(cur);
  };

  // completion heuristics for pills
  const reqFilled = keys => keys.filter(k => form[k] && String(form[k]).trim()).length;
  const osnovniPill = `${reqFilled(['tip', 'naslov', 'adresa', 'opstina', 'cena'])}/5`;
  const infoFilled = reqFilled(['povrsina', 'sprat', 'godina', 'visina']);
  const opremaCount = Object.values(oprema).filter(Boolean).length + Object.values(dodatni).filter(Boolean).length;
  const stepDone = {
    'sec-osnovni': reqFilled(['tip', 'naslov', 'adresa', 'opstina', 'cena']) === 5,
    'sec-info': infoFilled >= 3,
    'sec-uslovi': !!form.depozit,
    'sec-troskovi': !!(form.infostan || form.struja),
    'sec-oprema': opremaCount > 0,
    'sec-foto': counts.filledReq >= counts.required && counts.required > 0
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "shell"
  }, /*#__PURE__*/React.createElement("div", {
    className: "app-bg"
  }), /*#__PURE__*/React.createElement("div", {
    className: 'scrim' + (navOpen ? ' show' : ''),
    onClick: () => setNavOpen(false)
  }), /*#__PURE__*/React.createElement("aside", {
    className: 'sidebar' + (navOpen ? ' show' : '')
  }, /*#__PURE__*/React.createElement("div", {
    className: "brand"
  }, /*#__PURE__*/React.createElement("span", {
    className: "brand-mark"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "shield",
    size: 16,
    style: {
      color: '#0A1628'
    }
  })), /*#__PURE__*/React.createElement("span", {
    className: "brand-word"
  }, "Rent", /*#__PURE__*/React.createElement("span", null, "Check"))), /*#__PURE__*/React.createElement("div", {
    className: "nav-label"
  }, "Stanodavac"), /*#__PURE__*/React.createElement("button", {
    className: "nav-item"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "layout-grid",
    size: 16
  }), "Pregled"), /*#__PURE__*/React.createElement("button", {
    className: "nav-item active"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "building-2",
    size: 16
  }), "Nekretnine", /*#__PURE__*/React.createElement("span", {
    className: "nav-count"
  }, "7")), /*#__PURE__*/React.createElement("button", {
    className: "nav-item"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "users",
    size: 16
  }), "Stanari", /*#__PURE__*/React.createElement("span", {
    className: "nav-count"
  }, "6")), /*#__PURE__*/React.createElement("button", {
    className: "nav-item"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "star",
    size: 16
  }), "Recenzije"), /*#__PURE__*/React.createElement("button", {
    className: "nav-item"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "eye",
    size: 16
  }), "Pregledi"), /*#__PURE__*/React.createElement("div", {
    className: "nav-label"
  }, "Nalog"), /*#__PURE__*/React.createElement("button", {
    className: "nav-item"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "bar-chart",
    size: 16
  }), "Analitika"), /*#__PURE__*/React.createElement("button", {
    className: "nav-item"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "settings",
    size: 16
  }), "Pode\u0161avanja"), /*#__PURE__*/React.createElement("div", {
    className: "sidebar-foot"
  }, /*#__PURE__*/React.createElement("div", {
    className: "user-chip"
  }, /*#__PURE__*/React.createElement("span", {
    className: "avatar"
  }, "MM"), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("div", {
    className: "user-name"
  }, "Marko Markovi\u0107"), /*#__PURE__*/React.createElement("div", {
    className: "user-meta"
  }, "Gold verifikovan"))))), /*#__PURE__*/React.createElement("div", {
    className: "main"
  }, /*#__PURE__*/React.createElement("header", {
    className: "topbar"
  }, /*#__PURE__*/React.createElement("button", {
    className: "icon-btn menu-btn",
    onClick: () => setNavOpen(true)
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "menu",
    size: 18
  })), /*#__PURE__*/React.createElement("div", {
    className: "crumbs"
  }, /*#__PURE__*/React.createElement("b", null, "Nekretnine"), /*#__PURE__*/React.createElement(Icon, {
    name: "chevron-right",
    size: 13
  }), "Nova"), /*#__PURE__*/React.createElement("div", {
    className: "topbar-spacer"
  }), /*#__PURE__*/React.createElement("button", {
    className: "icon-btn",
    title: "Pomo\u0107"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "circle-help",
    size: 17
  })), /*#__PURE__*/React.createElement("button", {
    className: "icon-btn",
    title: "Obave\u0161tenja"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "bell",
    size: 17
  }))), /*#__PURE__*/React.createElement("div", {
    className: "scroll-area",
    ref: scrollRef,
    onScroll: onScroll
  }, /*#__PURE__*/React.createElement("div", {
    className: "content"
  }, /*#__PURE__*/React.createElement("div", {
    className: "page-head"
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "building-2",
    size: 13
  }), "Novi oglas"), /*#__PURE__*/React.createElement("h1", {
    className: "page-title"
  }, "Dodaj nekretninu"), /*#__PURE__*/React.createElement("p", {
    className: "page-sub"
  }, "Popuni podatke o nekretnini. Slotovi za fotografije se automatski prilago\u0111avaju izabranom tipu i strukturi.")), /*#__PURE__*/React.createElement("div", {
    className: "stepper"
  }, STEPS.map((s, i) => /*#__PURE__*/React.createElement("button", {
    key: s.id,
    className: 'step' + (activeStep === s.id ? ' active' : '') + (stepDone[s.id] ? ' done' : ''),
    onClick: () => scrollTo(s.id)
  }, /*#__PURE__*/React.createElement("span", {
    className: "step-num"
  }, stepDone[s.id] ? /*#__PURE__*/React.createElement(Icon, {
    name: "check",
    size: 13
  }) : String(i + 1).padStart(2, '0')), /*#__PURE__*/React.createElement("span", {
    className: "step-text"
  }, /*#__PURE__*/React.createElement("span", {
    className: "step-name"
  }, s.name), /*#__PURE__*/React.createElement("span", {
    className: "step-meta"
  }, s.meta))))), /*#__PURE__*/React.createElement(Section, {
    id: "sec-osnovni",
    icon: "home",
    title: "Osnovni podaci",
    desc: "Tip, naslov i lokacija oglasa",
    pill: osnovniPill,
    pillGood: stepDone['sec-osnovni'],
    open: open['sec-osnovni'],
    onToggle: () => setOpen(o => ({
      ...o,
      'sec-osnovni': !o['sec-osnovni']
    }))
  }, /*#__PURE__*/React.createElement("div", {
    className: "grid"
  }, /*#__PURE__*/React.createElement(Field, {
    label: "Tip nekretnine",
    required: true
  }, /*#__PURE__*/React.createElement(Select, {
    value: form.tip,
    options: TIP_OPTS,
    onChange: v => set('tip', v)
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Struktura",
    required: form.tip === 'stan',
    hint: form.tip !== 'stan' ? 'Dostupno samo za stanove' : 'Određuje broj spavaćih soba'
  }, /*#__PURE__*/React.createElement(Select, {
    value: form.struktura,
    options: STRUKTURA_OPTS,
    disabled: form.tip !== 'stan',
    onChange: v => set('struktura', v)
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Naslov oglasa",
    required: true,
    span2: true
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.naslov,
    onChange: v => set('naslov', v),
    placeholder: "npr. Svetao dvosoban stan, Vra\u010Dar \u2014 52m\xB2"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Adresa",
    required: true
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.adresa,
    onChange: v => set('adresa', v),
    leadIcon: "map-pin",
    placeholder: "Ulica i broj"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Op\u0161tina",
    required: true
  }, /*#__PURE__*/React.createElement(Select, {
    value: form.opstina,
    options: OPSTINA_OPTS,
    onChange: v => set('opstina', v)
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Cena",
    required: true,
    hint: "Mese\u010Dna zakupnina"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.cena,
    onChange: v => set('cena', v),
    affix: "EUR / mes",
    placeholder: "600",
    type: "number"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Povr\u0161ina",
    required: true
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.povrsina,
    onChange: v => set('povrsina', v),
    affix: "m\xB2",
    placeholder: "52",
    type: "number"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Opis",
    span2: true,
    hint: "Istakni prednosti \u2014 orijentacija, renoviranje, mir, blizina prevoza"
  }, /*#__PURE__*/React.createElement(TextInput, {
    textarea: true,
    value: form.opis,
    onChange: v => set('opis', v),
    placeholder: "Opi\u0161i nekretninu..."
  })))), /*#__PURE__*/React.createElement(Section, {
    id: "sec-info",
    icon: "list",
    title: "Osnovne informacije",
    desc: "Detalji o prostoru i instalacijama",
    pill: `${infoFilled}/4`,
    pillGood: stepDone['sec-info'],
    open: open['sec-info'],
    onToggle: () => setOpen(o => ({
      ...o,
      'sec-info': !o['sec-info']
    }))
  }, /*#__PURE__*/React.createElement("div", {
    className: "grid-3"
  }, /*#__PURE__*/React.createElement(Field, {
    label: "Sprat"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.sprat,
    onChange: v => set('sprat', v),
    placeholder: "npr. 3 / PR / VPR"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Ukupno spratova"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.ukupnoSpratova,
    onChange: v => set('ukupnoSpratova', v),
    placeholder: "6",
    type: "number"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Godina gradnje"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.godina,
    onChange: v => set('godina', v),
    placeholder: "1985",
    type: "number"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Visina plafona"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.visina,
    onChange: v => set('visina', v),
    affix: "m",
    placeholder: "2.8",
    type: "number"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Name\u0161tenost"
  }, /*#__PURE__*/React.createElement(Select, {
    value: form.namestenost,
    options: NAMESTENOST,
    onChange: v => set('namestenost', v)
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Stolarija"
  }, /*#__PURE__*/React.createElement(Select, {
    value: form.stolarija,
    options: STOLARIJA,
    onChange: v => set('stolarija', v)
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Spava\u0107e sobe"
  }, /*#__PURE__*/React.createElement(NumStepper, {
    value: form.spavace,
    onChange: v => set('spavace', v)
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Kupatila"
  }, /*#__PURE__*/React.createElement(NumStepper, {
    value: form.kupatila,
    onChange: v => set('kupatila', v),
    min: 1
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Grejanje"
  }, /*#__PURE__*/React.createElement(Select, {
    value: form.grejanje,
    options: GREJANJE,
    onChange: v => set('grejanje', v)
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Parking zona",
    span2: true
  }, /*#__PURE__*/React.createElement(Select, {
    value: form.parking,
    options: PARKING,
    onChange: v => set('parking', v)
  }))), /*#__PURE__*/React.createElement("div", {
    className: "subgroup-label"
  }, "Dodatni prostori"), /*#__PURE__*/React.createElement("div", {
    className: "chips"
  }, DODATNI_PROSTORI.map(t => /*#__PURE__*/React.createElement(Chip, {
    key: t.id,
    label: t.label,
    icon: t.icon,
    on: !!dodatni[t.id],
    onClick: () => toggle(dodatni, setDodatni, t.id)
  })))), /*#__PURE__*/React.createElement(Section, {
    id: "sec-uslovi",
    icon: "calendar",
    title: "Uslovi zakupa",
    desc: "Depozit, period i pravila",
    pill: stepDone['sec-uslovi'] ? 'Popunjeno' : 'Opciono',
    pillGood: stepDone['sec-uslovi'],
    open: open['sec-uslovi'],
    onToggle: () => setOpen(o => ({
      ...o,
      'sec-uslovi': !o['sec-uslovi']
    }))
  }, /*#__PURE__*/React.createElement("div", {
    className: "grid"
  }, /*#__PURE__*/React.createElement(Field, {
    label: "Depozit",
    hint: "Naj\u010De\u0161\u0107e u visini jedne zakupnine"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.depozit,
    onChange: v => set('depozit', v),
    affix: "EUR",
    placeholder: "600",
    type: "number"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Minimalni zakup"
  }, /*#__PURE__*/React.createElement(Select, {
    value: form.minZakup,
    options: MIN_ZAKUP,
    onChange: v => set('minZakup', v)
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Period pla\u0107anja"
  }, /*#__PURE__*/React.createElement(Select, {
    value: form.period,
    options: PERIOD,
    onChange: v => set('period', v)
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Useljivo od"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.useljivo,
    onChange: v => set('useljivo', v),
    type: "date"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Ljubimci",
    span2: true
  }, /*#__PURE__*/React.createElement(Segmented, {
    value: form.ljubimci,
    options: LJUBIMCI,
    onChange: v => set('ljubimci', v)
  })))), /*#__PURE__*/React.createElement(Section, {
    id: "sec-troskovi",
    icon: "euro",
    title: "Tro\u0161kovi",
    desc: "Mese\u010Dne re\u017Eije i komunalije",
    pill: stepDone['sec-troskovi'] ? 'Popunjeno' : 'Opciono',
    pillGood: stepDone['sec-troskovi'],
    open: open['sec-troskovi'],
    onToggle: () => setOpen(o => ({
      ...o,
      'sec-troskovi': !o['sec-troskovi']
    }))
  }, /*#__PURE__*/React.createElement("div", {
    className: "grid-3"
  }, /*#__PURE__*/React.createElement(Field, {
    label: "Infostan",
    hint: "Prose\u010Dno mese\u010Dno"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.infostan,
    onChange: v => set('infostan', v),
    affix: "EUR",
    placeholder: "45",
    type: "number"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Struja",
    hint: "Prose\u010Dno mese\u010Dno"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.struja,
    onChange: v => set('struja', v),
    affix: "EUR",
    placeholder: "30",
    type: "number"
  })), /*#__PURE__*/React.createElement(Field, {
    label: "Kablovska + Internet"
  }, /*#__PURE__*/React.createElement(TextInput, {
    value: form.internet,
    onChange: v => set('internet', v),
    affix: "EUR",
    placeholder: "20",
    type: "number"
  })))), /*#__PURE__*/React.createElement(Section, {
    id: "sec-oprema",
    icon: "sparkles",
    title: "Opremljenost",
    desc: "\u0160ta dolazi uz nekretninu i objekat",
    pill: opremaCount ? `${opremaCount} izabrano` : 'Opciono',
    pillGood: opremaCount > 0,
    open: open['sec-oprema'],
    onToggle: () => setOpen(o => ({
      ...o,
      'sec-oprema': !o['sec-oprema']
    }))
  }, /*#__PURE__*/React.createElement("div", {
    className: "subgroup-label"
  }, "Oprema nekretnine"), /*#__PURE__*/React.createElement("div", {
    className: "chips"
  }, OPREMA_NEKRETNINE.map(t => /*#__PURE__*/React.createElement(Chip, {
    key: t.id,
    label: t.label,
    icon: t.icon,
    on: !!oprema['n_' + t.id],
    onClick: () => toggle(oprema, setOprema, 'n_' + t.id)
  }))), /*#__PURE__*/React.createElement("div", {
    className: "subgroup-label"
  }, "Oprema objekta"), /*#__PURE__*/React.createElement("div", {
    className: "chips"
  }, OPREMA_OBJEKTA.map(t => /*#__PURE__*/React.createElement(Chip, {
    key: t.id,
    label: t.label,
    icon: t.icon,
    on: !!oprema['o_' + t.id],
    onClick: () => toggle(oprema, setOprema, 'o_' + t.id)
  })))), /*#__PURE__*/React.createElement(Section, {
    id: "sec-foto",
    icon: "camera",
    title: "Fotografije",
    desc: "Vo\u0111eni slotovi po prostorijama",
    pill: `${counts.filledReq}/${counts.required}`,
    pillGood: stepDone['sec-foto'],
    open: open['sec-foto'],
    onToggle: () => setOpen(o => ({
      ...o,
      'sec-foto': !o['sec-foto']
    }))
  }, /*#__PURE__*/React.createElement(PhotoUpload, {
    plan: plan,
    photos: photos,
    onUpload: onUpload,
    onRemove: onRemove
  })))), /*#__PURE__*/React.createElement("div", {
    className: "actionbar"
  }, /*#__PURE__*/React.createElement("div", {
    className: "action-status"
  }, /*#__PURE__*/React.createElement("span", {
    className: "dot"
  }), counts.filledReq >= counts.required && counts.required > 0 ? /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", {
    style: {
      color: 'var(--accent-tint)',
      fontWeight: 500
    }
  }, "Spremno za objavu"), " \xB7 ", counts.filledTotal, " ", counts.filledTotal === 1 ? 'slika' : 'slika', " dodato") : /*#__PURE__*/React.createElement("span", null, counts.filledReq, "/", counts.required, " obaveznih slika \xB7 nacrt se \u010Duva automatski")), /*#__PURE__*/React.createElement("div", {
    className: "action-spacer"
  }), /*#__PURE__*/React.createElement("button", {
    className: "btn btn-ghost save-draft"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "save",
    size: 16
  }), "Sa\u010Duvaj nacrt"), /*#__PURE__*/React.createElement("button", {
    className: "btn btn-secondary"
  }, "Otka\u017Ei"), /*#__PURE__*/React.createElement("button", {
    className: "btn btn-primary"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "check",
    size: 16
  }), "Dodaj nekretninu"))), /*#__PURE__*/React.createElement(TweaksPanel, null, /*#__PURE__*/React.createElement(TweakSection, {
    label: "Akcenat"
  }), /*#__PURE__*/React.createElement(TweakColor, {
    label: "Boja akcenta",
    value: t.accent,
    options: ['#1D9E75', '#378ADD', '#1FB6C9', '#7A5AE0'],
    onChange: v => setTweak('accent', v)
  }), /*#__PURE__*/React.createElement(TweakSection, {
    label: "Povr\u0161ina"
  }), /*#__PURE__*/React.createElement(TweakSlider, {
    label: "Zamu\u0107enje stakla",
    value: t.glass,
    min: 0,
    max: 28,
    unit: "px",
    onChange: v => setTweak('glass', v)
  }), /*#__PURE__*/React.createElement(TweakRadio, {
    label: "Zaobljenost",
    value: t.radius,
    options: ['Oštro', 'Meko', 'Zaobljeno'],
    onChange: v => setTweak('radius', v)
  })));
}
ReactDOM.createRoot(document.getElementById('root')).render(/*#__PURE__*/React.createElement(App, null));
})(); } catch (e) { __ds_ns.__errors.push({ path: "add-property/app.jsx", error: String((e && e.message) || e) }); }

// add-property/controls.jsx
try { (() => {
// RentCheck — Form controls
// Field wrapper, TextInput, NumberInput, Select (custom), Segmented, Chip, Section

const {
  useState,
  useRef,
  useEffect
} = React;

// ── Field wrapper ──────────────────────────────────────────────────────────
function Field({
  label,
  required,
  hint,
  span2,
  children
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: 'field' + (span2 ? ' col-2' : '')
  }, label && /*#__PURE__*/React.createElement("label", {
    className: "field-label"
  }, label, required && /*#__PURE__*/React.createElement("span", {
    className: "req"
  }, "*")), children, hint && /*#__PURE__*/React.createElement("span", {
    className: "field-hint"
  }, hint));
}

// ── Text input ─────────────────────────────────────────────────────────────
function TextInput({
  value,
  onChange,
  placeholder,
  affix,
  leadIcon,
  type = 'text',
  textarea
}) {
  if (textarea) {
    return /*#__PURE__*/React.createElement("textarea", {
      className: "textarea",
      placeholder: placeholder,
      value: value || '',
      onChange: e => onChange(e.target.value)
    });
  }
  const input = /*#__PURE__*/React.createElement("input", {
    className: "input",
    type: type,
    placeholder: placeholder,
    value: value || '',
    onChange: e => onChange(e.target.value)
  });
  if (affix) {
    return /*#__PURE__*/React.createElement("div", {
      className: "input-affix"
    }, input, /*#__PURE__*/React.createElement("span", {
      className: "affix"
    }, affix));
  }
  if (leadIcon) {
    return /*#__PURE__*/React.createElement("div", {
      className: "input-affix lead"
    }, /*#__PURE__*/React.createElement("span", {
      className: "affix affix-lead"
    }, /*#__PURE__*/React.createElement(Icon, {
      name: leadIcon,
      size: 15
    })), input);
  }
  return input;
}

// ── Custom Select ───────────────────────────────────────────────────────────
// options: [{ value, label, icon?, sub? }]
function Select({
  value,
  onChange,
  options,
  placeholder = 'Izaberi',
  disabled
}) {
  const [open, setOpen] = useState(false);
  const ref = useRef(null);
  useEffect(() => {
    if (!open) return;
    const onDoc = e => {
      if (ref.current && !ref.current.contains(e.target)) setOpen(false);
    };
    document.addEventListener('mousedown', onDoc);
    return () => document.removeEventListener('mousedown', onDoc);
  }, [open]);
  const sel = options.find(o => o.value === value);
  return /*#__PURE__*/React.createElement("div", {
    className: 'select' + (open ? ' open' : '') + (disabled ? ' disabled' : ''),
    ref: ref
  }, /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "select-trigger",
    onClick: () => !disabled && setOpen(v => !v)
  }, sel && sel.icon && /*#__PURE__*/React.createElement(Icon, {
    name: sel.icon,
    size: 16,
    className: "sv-ico"
  }), sel ? /*#__PURE__*/React.createElement("span", {
    className: "val"
  }, sel.label) : /*#__PURE__*/React.createElement("span", {
    className: "val ph"
  }, placeholder), /*#__PURE__*/React.createElement(Icon, {
    name: "chevron-down",
    size: 16,
    className: "chev"
  })), open && /*#__PURE__*/React.createElement("div", {
    className: "select-menu"
  }, options.map(o => /*#__PURE__*/React.createElement("button", {
    type: "button",
    key: o.value,
    className: 'opt' + (o.value === value ? ' sel' : ''),
    onClick: () => {
      onChange(o.value);
      setOpen(false);
    }
  }, o.icon && /*#__PURE__*/React.createElement(Icon, {
    name: o.icon,
    size: 16,
    className: "opt-ico"
  }), /*#__PURE__*/React.createElement("span", null, o.label), o.sub && /*#__PURE__*/React.createElement("span", {
    className: "opt-sub"
  }, o.sub), o.value === value && /*#__PURE__*/React.createElement(Icon, {
    name: "check",
    size: 15,
    className: "opt-check"
  })))));
}

// ── Segmented control ────────────────────────────────────────────────────────
function Segmented({
  value,
  onChange,
  options
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "segmented"
  }, options.map(o => /*#__PURE__*/React.createElement("button", {
    type: "button",
    key: o.value,
    className: 'seg' + (o.value === value ? ' on' : ''),
    onClick: () => onChange(o.value)
  }, o.icon && /*#__PURE__*/React.createElement(Icon, {
    name: o.icon,
    size: 15
  }), o.label)));
}

// ── Chip (toggle tag) ─────────────────────────────────────────────────────────
function Chip({
  label,
  icon,
  on,
  onClick
}) {
  return /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: 'chip' + (on ? ' on' : ''),
    onClick: onClick
  }, on ? /*#__PURE__*/React.createElement(Icon, {
    name: "check",
    size: 14,
    className: "chip-ico chip-check"
  }) : icon && /*#__PURE__*/React.createElement(Icon, {
    name: icon,
    size: 14,
    className: "chip-ico"
  }), label);
}

// ── Accordion Section ──────────────────────────────────────────────────────────
function Section({
  id,
  icon,
  title,
  desc,
  pill,
  pillGood,
  open,
  onToggle,
  sectionRef,
  children
}) {
  return /*#__PURE__*/React.createElement("section", {
    className: 'section' + (open ? ' open' : ''),
    id: id,
    ref: sectionRef
  }, /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "section-head",
    onClick: onToggle
  }, /*#__PURE__*/React.createElement("span", {
    className: "section-ico"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: icon,
    size: 19
  })), /*#__PURE__*/React.createElement("span", {
    className: "section-titles"
  }, /*#__PURE__*/React.createElement("span", {
    className: "section-title"
  }, title), desc && /*#__PURE__*/React.createElement("span", {
    className: "section-desc"
  }, desc)), pill && /*#__PURE__*/React.createElement("span", {
    className: 'section-pill' + (pillGood ? ' good' : '')
  }, pill), /*#__PURE__*/React.createElement(Icon, {
    name: "chevron-down",
    size: 20,
    className: "section-chev"
  })), open && /*#__PURE__*/React.createElement("div", {
    className: "section-body"
  }, children));
}
Object.assign(window, {
  Field,
  TextInput,
  Select,
  Segmented,
  Chip,
  Section
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "add-property/controls.jsx", error: String((e && e.message) || e) }); }

// add-property/icons.jsx
try { (() => {
// RentCheck — Icon set (Lucide-accurate paths, 24×24, 2px stroke, round caps)
// Usage: <Icon name="camera" size={18} />

const ICON_PATHS = {
  // nav / chrome
  'layout-grid': '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
  'building-2': '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
  'users': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
  'star': '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
  'bar-chart': '<line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/>',
  'settings': '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
  'bell': '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
  'log-out': '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
  'menu': '<line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/>',
  'eye': '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
  // chevrons / arrows / marks
  'chevron-down': '<path d="m6 9 6 6 6-6"/>',
  'chevron-left': '<path d="m15 18-6-6 6-6"/>',
  'chevron-right': '<path d="m9 18 6-6-6-6"/>',
  'arrow-right': '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
  'check': '<path d="M20 6 9 17l-5-5"/>',
  'check-circle': '<path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/>',
  'x': '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
  'plus': '<path d="M5 12h14"/><path d="M12 5v14"/>',
  'info': '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
  'sparkles': '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .962 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.962 0z"/>',
  // property types
  'home': '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
  'house': '<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
  'door-open': '<path d="M13 4h3a2 2 0 0 1 2 2v14"/><path d="M2 20h3"/><path d="M13 20h9"/><path d="M10 12v.01"/><path d="M13 4.562v16.157a1 1 0 0 1-1.242.97L5 20V5.562a2 2 0 0 1 1.515-1.94l4-1A2 2 0 0 1 13 4.561Z"/>',
  'briefcase': '<path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/>',
  'store': '<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/>',
  'ship': '<path d="M12 10.189V14"/><path d="M12 2v3"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-8.188-3.639a2 2 0 0 0-1.624 0L3 14a11.6 11.6 0 0 0 2.81 7.76"/><path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1s1.2 1 2.5 1c2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>',
  'tent': '<path d="M3.5 21 14 3"/><path d="M20.5 21 10 3"/><path d="M15.5 21 12 15l-3.5 6"/><path d="M2 21h20"/>',
  // rooms
  'sofa': '<path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/><path d="M2 11v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v2H6v-2a2 2 0 0 0-4 0Z"/><path d="M4 18v2"/><path d="M20 18v2"/><path d="M12 4v9"/>',
  'cooking-pot': '<path d="M2 12h20"/><path d="M18 12v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-8"/><path d="m4 8 16-4"/><path d="m8.86 6.78-.45-1.81a2 2 0 0 1 1.45-2.43l1.94-.48a2 2 0 0 1 2.43 1.46l.45 1.8"/>',
  'bath': '<path d="M10 4 8 6"/><path d="M17 19v2"/><path d="M2 12h20"/><path d="M7 19v2"/><path d="M9 5 7.621 3.621A2.121 2.121 0 0 0 4 5v7"/><path d="M20 12v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3"/>',
  'bed-double': '<path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M12 4v6"/><path d="M2 18h20"/>',
  'bed': '<path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/>',
  'trees': '<path d="M10 10v.2A3 3 0 0 1 8.9 16H5a3 3 0 0 1-1-5.8V10a3 3 0 0 1 6 0Z"/><path d="M7 16v6"/><path d="M13 19v3"/><path d="M12 19h8.3a1 1 0 0 0 .7-1.7L18 14h.3a1 1 0 0 0 .7-1.7L16 9h.2a1 1 0 0 0 .8-1.7L13 3l-1.4 1.5"/>',
  'mountain': '<path d="m8 3 4 8 5-5 5 15H2L8 3z"/>',
  'warehouse': '<path d="M22 8.35V20a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/><rect width="12" height="12" x="6" y="10"/>',
  // info fields
  'ruler': '<path d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.41 2.41 0 0 1 0-3.4l2.6-2.6a2.41 2.41 0 0 1 3.4 0Z"/><path d="m14.5 12.5 2-2"/><path d="m11.5 9.5 2-2"/><path d="m8.5 6.5 2-2"/><path d="m17.5 15.5 2-2"/>',
  'layers': '<path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>',
  'calendar': '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>',
  'euro': '<path d="M4 10h12"/><path d="M4 14h9"/><path d="M19 6a7.7 7.7 0 0 0-5.2-2A7.5 7.5 0 0 0 6 12a7.5 7.5 0 0 0 7.8 8 7.7 7.7 0 0 0 5.2-2"/>',
  'map-pin': '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
  'thermometer': '<path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>',
  'car': '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>',
  'maximize': '<path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/>',
  'package': '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><polyline points="3.29 7 12 12 20.71 7"/><path d="m7.5 4.27 9 5.15"/>',
  // amenities
  'wind': '<path d="M12.8 19.6A2 2 0 1 0 14 16H2"/><path d="M17.5 8a2.5 2.5 0 1 1 2 4H2"/><path d="M9.8 4.4A2 2 0 1 1 11 8H2"/>',
  'wifi': '<path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.859a10 10 0 0 1 14 0"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/>',
  'zap': '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
  'tv': '<rect width="20" height="15" x="2" y="3" rx="2"/><polyline points="17 21 12 18 7 21"/>',
  'droplets': '<path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 4.8 7 3c-.29 1.8-1.14 3.13-2.29 4.06S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"/><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"/>',
  'flame': '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
  'lock': '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
  'shield': '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>',
  'arrow-up-down': '<path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/>',
  'phone': '<path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/>',
  'utensils': '<path d="M3 2v7c0 1.1.9 2 2 2h2.5"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>',
  // pets / misc
  'paw': '<circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"/>',
  'camera': '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
  'image': '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
  'images': '<path d="M18 22H4a2 2 0 0 1-2-2V6"/><path d="m22 13-1.296-1.296a2.41 2.41 0 0 0-3.408 0L11 18"/><circle cx="12" cy="8" r="2"/><rect width="16" height="16" x="6" y="2" rx="2"/>',
  'upload': '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>',
  'trash': '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
  'save': '<path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/>',
  'pen': '<path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>',
  'list': '<path d="M3 12h.01"/><path d="M3 18h.01"/><path d="M3 6h.01"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M8 6h13"/>',
  'circle-help': '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>'
};
function Icon({
  name,
  size = 18,
  strokeWidth = 1.9,
  style,
  className
}) {
  const d = ICON_PATHS[name] || ICON_PATHS['image'];
  return /*#__PURE__*/React.createElement("svg", {
    width: size,
    height: size,
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: strokeWidth,
    strokeLinecap: "round",
    strokeLinejoin: "round",
    style: style,
    className: className,
    "aria-hidden": "true",
    dangerouslySetInnerHTML: {
      __html: ICON_PATHS[name] || ICON_PATHS['image']
    }
  });
}
Object.assign(window, {
  Icon,
  ICON_PATHS
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "add-property/icons.jsx", error: String((e && e.message) || e) }); }

// add-property/photo-upload.jsx
try { (() => {
// RentCheck — Smart dynamic photo upload
// buildPhotoPlan() derives required room groups + slots from property type & structure.

// Bedrooms implied by apartment structure (per RentCheck listing rules)
function bedroomsForStruktura(s) {
  return {
    garsonjera: 0,
    jednosoban: 0,
    jednoiposoban: 0,
    dvosoban: 1,
    dvoiposoban: 1,
    trosoban: 2,
    troiposoban: 2,
    cetvorosoban: 3,
    cetvoroiposoban: 3,
    petosoban: 4
  }[s] ?? 0;
}
function slot(gid, i, label, sub) {
  return {
    id: `${gid}-${i}`,
    label,
    sub
  };
}

// Returns [{ id, label, icon, tag: 'req'|'opt'|'new', tagLabel, slots: [{id,label,sub}] }]
function buildPhotoPlan(tip, struktura) {
  const groups = [];
  const interiorBase = () => {
    groups.push({
      id: 'dnevna',
      label: 'Dnevna soba',
      icon: 'sofa',
      tag: 'req',
      slots: [slot('dnevna', 1, 'Ugao 1', 'Širi kadar'), slot('dnevna', 2, 'Ugao 2', 'Suprotni ugao')]
    });
    groups.push({
      id: 'kuhinja',
      label: 'Kuhinja',
      icon: 'cooking-pot',
      tag: 'req',
      slots: [slot('kuhinja', 1, 'Ugao 1', 'Radni deo'), slot('kuhinja', 2, 'Ugao 2', 'Trpezarija')]
    });
    groups.push({
      id: 'kupatilo',
      label: 'Kupatilo',
      icon: 'bath',
      tag: 'req',
      slots: [slot('kupatilo', 1, 'Iz više uglova', 'Tuš / kada'), slot('kupatilo', 2, 'Detalj', 'Lavabo / WC')]
    });
  };
  const addBedrooms = (n, dynamic) => {
    for (let b = 1; b <= n; b++) {
      groups.push({
        id: `spavaca${b}`,
        label: n > 1 ? `Spavaća soba ${b}` : 'Spavaća soba',
        icon: 'bed-double',
        tag: dynamic ? 'new' : 'req',
        tagLabel: dynamic ? 'Dodato strukturom' : undefined,
        slots: [slot(`spavaca${b}`, 1, 'Ugao 1', 'Krevet'), slot(`spavaca${b}`, 2, 'Ugao 2', 'Suprotni ugao')]
      });
    }
  };
  switch (tip) {
    case 'stan':
      {
        interiorBase();
        const beds = bedroomsForStruktura(struktura);
        addBedrooms(beds, true);
        if (beds >= 3) {
          groups.push({
            id: 'kupatilo2',
            label: 'Dodatno kupatilo',
            icon: 'bath',
            tag: 'new',
            tagLabel: 'Dodato strukturom',
            slots: [slot('kupatilo2', 1, 'Iz više uglova', 'Drugo kupatilo')]
          });
        }
        groups.push({
          id: 'terasa',
          label: 'Terasa / Balkon',
          icon: 'maximize',
          tag: 'opt',
          slots: [slot('terasa', 1, 'Pogled', 'Opciono')]
        });
        break;
      }
    case 'kuca':
    case 'vikendica':
      {
        interiorBase();
        addBedrooms(tip === 'vikendica' ? 1 : 2, true);
        groups.push({
          id: 'dvoriste',
          label: 'Dvorište',
          icon: 'trees',
          tag: 'new',
          tagLabel: 'Tip: ' + (tip === 'kuca' ? 'Kuća' : 'Vikendica'),
          slots: [slot('dvoriste', 1, 'Prednje', 'Prilaz'), slot('dvoriste', 2, 'Zadnje', 'Bašta')]
        });
        groups.push({
          id: 'spolja',
          label: 'Spoljašnost',
          icon: 'house',
          tag: 'new',
          tagLabel: 'Fasada',
          slots: [slot('spolja', 1, 'Fasada', 'Cela kuća')]
        });
        break;
      }
    case 'soba':
      {
        groups.push({
          id: 'soba',
          label: 'Soba',
          icon: 'bed',
          tag: 'req',
          slots: [slot('soba', 1, 'Ugao 1', 'Krevet'), slot('soba', 2, 'Ugao 2', 'Radni deo')]
        });
        groups.push({
          id: 'kupatilo',
          label: 'Kupatilo',
          icon: 'bath',
          tag: 'req',
          slots: [slot('kupatilo', 1, 'Iz više uglova', 'Zajedničko / odvojeno')]
        });
        groups.push({
          id: 'kuhinja',
          label: 'Zajednička kuhinja',
          icon: 'cooking-pot',
          tag: 'opt',
          slots: [slot('kuhinja', 1, 'Pregled', 'Opciono')]
        });
        break;
      }
    case 'poslovni':
      {
        groups.push({
          id: 'prostor',
          label: 'Radni prostor',
          icon: 'briefcase',
          tag: 'req',
          slots: [slot('prostor', 1, 'Ugao 1', 'Open space'), slot('prostor', 2, 'Ugao 2', 'Kancelarije')]
        });
        groups.push({
          id: 'ulaz',
          label: 'Ulaz / Recepcija',
          icon: 'door-open',
          tag: 'req',
          slots: [slot('ulaz', 1, 'Ulaz', 'Prijemni deo')]
        });
        groups.push({
          id: 'sanitarni',
          label: 'Sanitarni čvor',
          icon: 'bath',
          tag: 'req',
          slots: [slot('sanitarni', 1, 'Toalet', 'Sanitarije')]
        });
        break;
      }
    case 'lokal':
      {
        groups.push({
          id: 'izlog',
          label: 'Izlog',
          icon: 'store',
          tag: 'req',
          slots: [slot('izlog', 1, 'Spolja', 'Ulica / izlog')]
        });
        groups.push({
          id: 'prodajni',
          label: 'Prodajni prostor',
          icon: 'store',
          tag: 'req',
          slots: [slot('prodajni', 1, 'Ugao 1', 'Glavni deo'), slot('prodajni', 2, 'Ugao 2', 'Suprotni ugao')]
        });
        groups.push({
          id: 'magacin',
          label: 'Magacin / Ostava',
          icon: 'package',
          tag: 'opt',
          slots: [slot('magacin', 1, 'Pregled', 'Opciono')]
        });
        break;
      }
    case 'splav':
      {
        groups.push({
          id: 'enterijer',
          label: 'Enterijer',
          icon: 'sofa',
          tag: 'req',
          slots: [slot('enterijer', 1, 'Ugao 1', 'Unutra'), slot('enterijer', 2, 'Ugao 2', 'Bar / sto')]
        });
        groups.push({
          id: 'paluba',
          label: 'Paluba / Terasa',
          icon: 'ship',
          tag: 'new',
          tagLabel: 'Splav',
          slots: [slot('paluba', 1, 'Paluba', 'Pogled na vodu'), slot('paluba', 2, 'Terasa', 'Sedenje')]
        });
        groups.push({
          id: 'spoljaW',
          label: 'Spoljašnost sa vode',
          icon: 'ship',
          tag: 'new',
          tagLabel: 'Splav',
          slots: [slot('spoljaW', 1, 'Sa reke', 'Ceo objekat')]
        });
        break;
      }
    default:
      interiorBase();
  }
  return groups;
}
function planCounts(plan, photos) {
  let required = 0,
    filledReq = 0,
    optional = 0,
    filledOpt = 0;
  plan.forEach(g => g.slots.forEach(s => {
    const filled = !!photos[s.id];
    if (g.tag === 'opt') {
      optional++;
      if (filled) filledOpt++;
    } else {
      required++;
      if (filled) filledReq++;
    }
  }));
  return {
    required,
    filledReq,
    optional,
    filledOpt,
    total: required + optional,
    filledTotal: filledReq + filledOpt
  };
}

// ── Single slot ───────────────────────────────────────────────────────────────
function PhotoSlot({
  slot,
  src,
  onUpload,
  onRemove
}) {
  const inputRef = React.useRef(null);
  const [drag, setDrag] = React.useState(false);
  const handleFile = file => {
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = () => onUpload(slot.id, reader.result);
    reader.readAsDataURL(file);
  };
  if (src) {
    return /*#__PURE__*/React.createElement("div", {
      className: "slot filled"
    }, /*#__PURE__*/React.createElement("img", {
      className: "slot-img",
      src: src,
      alt: slot.label
    }), /*#__PURE__*/React.createElement("div", {
      className: "slot-grad"
    }), /*#__PURE__*/React.createElement("button", {
      type: "button",
      className: "slot-remove",
      title: "Ukloni",
      onClick: e => {
        e.stopPropagation();
        onRemove(slot.id);
      }
    }, /*#__PURE__*/React.createElement(Icon, {
      name: "trash",
      size: 13
    })), /*#__PURE__*/React.createElement("div", {
      className: "slot-caption"
    }, /*#__PURE__*/React.createElement(Icon, {
      name: "check-circle",
      size: 13,
      className: "ok"
    }), slot.label));
  }
  return /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: 'slot' + (drag ? ' drag' : ''),
    onClick: () => inputRef.current && inputRef.current.click(),
    onDragOver: e => {
      e.preventDefault();
      setDrag(true);
    },
    onDragLeave: () => setDrag(false),
    onDrop: e => {
      e.preventDefault();
      setDrag(false);
      handleFile(e.dataTransfer.files[0]);
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "slot-plus"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "camera",
    size: 18
  }), /*#__PURE__*/React.createElement("span", {
    className: "mini-plus"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "plus",
    size: 11,
    strokeWidth: 2.6
  }))), /*#__PURE__*/React.createElement("span", {
    className: "slot-label"
  }, slot.label), slot.sub && /*#__PURE__*/React.createElement("span", {
    className: "slot-sub"
  }, slot.sub), /*#__PURE__*/React.createElement("input", {
    ref: inputRef,
    type: "file",
    accept: "image/*",
    capture: "environment",
    style: {
      display: 'none'
    },
    onChange: e => handleFile(e.target.files[0])
  }));
}

// ── Full upload section ─────────────────────────────────────────────────────────
function PhotoUpload({
  plan,
  photos,
  onUpload,
  onRemove
}) {
  const c = planCounts(plan, photos);
  const pct = c.required ? Math.round(c.filledReq / c.required * 100) : 0;
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "photo-intro"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pi-icon"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "images",
    size: 20
  })), /*#__PURE__*/React.createElement("span", {
    className: "pi-text"
  }, /*#__PURE__*/React.createElement("span", {
    className: "pi-title"
  }, "Vo\u0111eno fotografisanje"), /*#__PURE__*/React.createElement("span", {
    className: "pi-desc"
  }, "Slotovi se prilago\u0111avaju tipu i strukturi nekretnine. Popuni obavezne uglove za kvalitetan oglas.")), /*#__PURE__*/React.createElement("span", {
    className: "meter"
  }, /*#__PURE__*/React.createElement("span", {
    className: "meter-top"
  }, /*#__PURE__*/React.createElement("span", {
    className: "meter-count"
  }, /*#__PURE__*/React.createElement("b", null, c.filledReq), "/", c.required), /*#__PURE__*/React.createElement("span", {
    className: "meter-label"
  }, "slika dodato", c.optional ? ` · +${c.filledOpt}/${c.optional} opciono` : '')), /*#__PURE__*/React.createElement("span", {
    className: "meter-track"
  }, /*#__PURE__*/React.createElement("span", {
    className: "meter-fill",
    style: {
      width: pct + '%'
    }
  })))), plan.map(g => {
    const filled = g.slots.filter(s => photos[s.id]).length;
    const tagClass = g.tag === 'opt' ? 'opt' : g.tag === 'new' ? 'new' : 'req';
    const tagText = g.tag === 'opt' ? 'Opciono' : g.tagLabel || 'Obavezno';
    return /*#__PURE__*/React.createElement("div", {
      className: "room-group",
      key: g.id
    }, /*#__PURE__*/React.createElement("div", {
      className: "room-head"
    }, /*#__PURE__*/React.createElement("span", {
      className: "room-ico"
    }, /*#__PURE__*/React.createElement(Icon, {
      name: g.icon,
      size: 17
    })), /*#__PURE__*/React.createElement("span", {
      className: "room-name"
    }, g.label), /*#__PURE__*/React.createElement("span", {
      className: 'room-tag ' + tagClass
    }, tagText), /*#__PURE__*/React.createElement("span", {
      className: "room-count"
    }, filled, "/", g.slots.length)), /*#__PURE__*/React.createElement("div", {
      className: "slot-grid"
    }, g.slots.map(s => /*#__PURE__*/React.createElement(PhotoSlot, {
      key: s.id,
      slot: s,
      src: photos[s.id],
      onUpload: onUpload,
      onRemove: onRemove
    }))));
  }));
}
Object.assign(window, {
  buildPhotoPlan,
  planCounts,
  PhotoUpload,
  PhotoSlot
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "add-property/photo-upload.jsx", error: String((e && e.message) || e) }); }

// add-property/tweaks-panel.jsx
try { (() => {
// @ds-adherence-ignore -- omelette starter scaffold (raw elements/hex/px by design)

/* BEGIN USAGE */
// tweaks-panel.jsx
// Reusable Tweaks shell + form-control helpers.
// Exports (to window): useTweaks, TweaksPanel, TweakSection, TweakRow, TweakSlider,
//   TweakToggle, TweakRadio, TweakSelect, TweakText, TweakNumber, TweakColor, TweakButton.
//
// Owns the host protocol (listens for __activate_edit_mode / __deactivate_edit_mode,
// posts __edit_mode_available / __edit_mode_set_keys / __edit_mode_dismissed) so
// individual prototypes don't re-roll it. Ships a consistent set of controls so you
// don't hand-draw <input type="range">, segmented radios, steppers, etc.
//
// Usage (in an HTML file that loads React + Babel):
//
//   const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
//     "primaryColor": "#D97757",
//     "palette": ["#D97757", "#29261b", "#f6f4ef"],
//     "fontSize": 16,
//     "density": "regular",
//     "dark": false
//   }/*EDITMODE-END*/;
//
//   function App() {
//     const [t, setTweak] = useTweaks(TWEAK_DEFAULTS);
//     return (
//       <div style={{ fontSize: t.fontSize, color: t.primaryColor }}>
//         Hello
//         <TweaksPanel>
//           <TweakSection label="Typography" />
//           <TweakSlider label="Font size" value={t.fontSize} min={10} max={32} unit="px"
//                        onChange={(v) => setTweak('fontSize', v)} />
//           <TweakRadio  label="Density" value={t.density}
//                        options={['compact', 'regular', 'comfy']}
//                        onChange={(v) => setTweak('density', v)} />
//           <TweakSection label="Theme" />
//           <TweakColor  label="Primary" value={t.primaryColor}
//                        options={['#D97757', '#2A6FDB', '#1F8A5B', '#7A5AE0']}
//                        onChange={(v) => setTweak('primaryColor', v)} />
//           <TweakColor  label="Palette" value={t.palette}
//                        options={[['#D97757', '#29261b', '#f6f4ef'],
//                                  ['#475569', '#0f172a', '#f1f5f9']]}
//                        onChange={(v) => setTweak('palette', v)} />
//           <TweakToggle label="Dark mode" value={t.dark}
//                        onChange={(v) => setTweak('dark', v)} />
//         </TweaksPanel>
//       </div>
//     );
//   }
//
// TweakRadio is the segmented control for 2–3 short options (auto-falls-back to
// TweakSelect past ~16/~10 chars per label); reach for TweakSelect directly when
// options are many or long. For color tweaks always curate 3-4 options rather than
// a free picker; an option can also be a whole 2–5 color palette (the stored value
// is the array). The Tweak* controls are a floor, not a ceiling — build custom
// controls inside the panel if a tweak calls for UI they don't cover.
/* END USAGE */
// ─────────────────────────────────────────────────────────────────────────────

const __TWEAKS_STYLE = `
  .twk-panel{position:fixed;right:16px;bottom:16px;z-index:2147483646;width:280px;
    max-height:calc(100vh - 32px);display:flex;flex-direction:column;
    transform:scale(var(--dc-inv-zoom,1));transform-origin:bottom right;
    background:rgba(250,249,247,.78);color:#29261b;
    -webkit-backdrop-filter:blur(24px) saturate(160%);backdrop-filter:blur(24px) saturate(160%);
    border:.5px solid rgba(255,255,255,.6);border-radius:14px;
    box-shadow:0 1px 0 rgba(255,255,255,.5) inset,0 12px 40px rgba(0,0,0,.18);
    font:11.5px/1.4 ui-sans-serif,system-ui,-apple-system,sans-serif;overflow:hidden}
  .twk-hd{display:flex;align-items:center;justify-content:space-between;
    padding:10px 8px 10px 14px;cursor:move;user-select:none}
  .twk-hd b{font-size:12px;font-weight:600;letter-spacing:.01em}
  .twk-x{appearance:none;border:0;background:transparent;color:rgba(41,38,27,.55);
    width:22px;height:22px;border-radius:6px;cursor:default;font-size:13px;line-height:1}
  .twk-x:hover{background:rgba(0,0,0,.06);color:#29261b}
  .twk-body{padding:2px 14px 14px;display:flex;flex-direction:column;gap:10px;
    overflow-y:auto;overflow-x:hidden;min-height:0;
    scrollbar-width:thin;scrollbar-color:rgba(0,0,0,.15) transparent}
  .twk-body::-webkit-scrollbar{width:8px}
  .twk-body::-webkit-scrollbar-track{background:transparent;margin:2px}
  .twk-body::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:4px;
    border:2px solid transparent;background-clip:content-box}
  .twk-body::-webkit-scrollbar-thumb:hover{background:rgba(0,0,0,.25);
    border:2px solid transparent;background-clip:content-box}
  .twk-row{display:flex;flex-direction:column;gap:5px}
  .twk-row-h{flex-direction:row;align-items:center;justify-content:space-between;gap:10px}
  .twk-lbl{display:flex;justify-content:space-between;align-items:baseline;
    color:rgba(41,38,27,.72)}
  .twk-lbl>span:first-child{font-weight:500}
  .twk-val{color:rgba(41,38,27,.5);font-variant-numeric:tabular-nums}

  .twk-sect{font-size:10px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;
    color:rgba(41,38,27,.45);padding:10px 0 0}
  .twk-sect:first-child{padding-top:0}

  .twk-field{appearance:none;box-sizing:border-box;width:100%;min-width:0;height:26px;padding:0 8px;
    border:.5px solid rgba(0,0,0,.1);border-radius:7px;
    background:rgba(255,255,255,.6);color:inherit;font:inherit;outline:none}
  .twk-field:focus{border-color:rgba(0,0,0,.25);background:rgba(255,255,255,.85)}
  select.twk-field{padding-right:22px;
    background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'><path fill='rgba(0,0,0,.5)' d='M0 0h10L5 6z'/></svg>");
    background-repeat:no-repeat;background-position:right 8px center}

  .twk-slider{appearance:none;-webkit-appearance:none;width:100%;height:4px;margin:6px 0;
    border-radius:999px;background:rgba(0,0,0,.12);outline:none}
  .twk-slider::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;
    width:14px;height:14px;border-radius:50%;background:#fff;
    border:.5px solid rgba(0,0,0,.12);box-shadow:0 1px 3px rgba(0,0,0,.2);cursor:default}
  .twk-slider::-moz-range-thumb{width:14px;height:14px;border-radius:50%;
    background:#fff;border:.5px solid rgba(0,0,0,.12);box-shadow:0 1px 3px rgba(0,0,0,.2);cursor:default}

  .twk-seg{position:relative;display:flex;padding:2px;border-radius:8px;
    background:rgba(0,0,0,.06);user-select:none}
  .twk-seg-thumb{position:absolute;top:2px;bottom:2px;border-radius:6px;
    background:rgba(255,255,255,.9);box-shadow:0 1px 2px rgba(0,0,0,.12);
    transition:left .15s cubic-bezier(.3,.7,.4,1),width .15s}
  .twk-seg.dragging .twk-seg-thumb{transition:none}
  .twk-seg button{appearance:none;position:relative;z-index:1;flex:1;border:0;
    background:transparent;color:inherit;font:inherit;font-weight:500;min-height:22px;
    border-radius:6px;cursor:default;padding:4px 6px;line-height:1.2;
    overflow-wrap:anywhere}

  .twk-toggle{position:relative;width:32px;height:18px;border:0;border-radius:999px;
    background:rgba(0,0,0,.15);transition:background .15s;cursor:default;padding:0}
  .twk-toggle[data-on="1"]{background:#34c759}
  .twk-toggle i{position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;
    background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.25);transition:transform .15s}
  .twk-toggle[data-on="1"] i{transform:translateX(14px)}

  .twk-num{display:flex;align-items:center;box-sizing:border-box;min-width:0;height:26px;padding:0 0 0 8px;
    border:.5px solid rgba(0,0,0,.1);border-radius:7px;background:rgba(255,255,255,.6)}
  .twk-num-lbl{font-weight:500;color:rgba(41,38,27,.6);cursor:ew-resize;
    user-select:none;padding-right:8px}
  .twk-num input{flex:1;min-width:0;height:100%;border:0;background:transparent;
    font:inherit;font-variant-numeric:tabular-nums;text-align:right;padding:0 8px 0 0;
    outline:none;color:inherit;-moz-appearance:textfield}
  .twk-num input::-webkit-inner-spin-button,.twk-num input::-webkit-outer-spin-button{
    -webkit-appearance:none;margin:0}
  .twk-num-unit{padding-right:8px;color:rgba(41,38,27,.45)}

  .twk-btn{appearance:none;height:26px;padding:0 12px;border:0;border-radius:7px;
    background:rgba(0,0,0,.78);color:#fff;font:inherit;font-weight:500;cursor:default}
  .twk-btn:hover{background:rgba(0,0,0,.88)}
  .twk-btn.secondary{background:rgba(0,0,0,.06);color:inherit}
  .twk-btn.secondary:hover{background:rgba(0,0,0,.1)}

  .twk-swatch{appearance:none;-webkit-appearance:none;width:56px;height:22px;
    border:.5px solid rgba(0,0,0,.1);border-radius:6px;padding:0;cursor:default;
    background:transparent;flex-shrink:0}
  .twk-swatch::-webkit-color-swatch-wrapper{padding:0}
  .twk-swatch::-webkit-color-swatch{border:0;border-radius:5.5px}
  .twk-swatch::-moz-color-swatch{border:0;border-radius:5.5px}

  .twk-chips{display:flex;gap:6px}
  .twk-chip{position:relative;appearance:none;flex:1;min-width:0;height:46px;
    padding:0;border:0;border-radius:6px;overflow:hidden;cursor:default;
    box-shadow:0 0 0 .5px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.06);
    transition:transform .12s cubic-bezier(.3,.7,.4,1),box-shadow .12s}
  .twk-chip:hover{transform:translateY(-1px);
    box-shadow:0 0 0 .5px rgba(0,0,0,.18),0 4px 10px rgba(0,0,0,.12)}
  .twk-chip[data-on="1"]{box-shadow:0 0 0 1.5px rgba(0,0,0,.85),
    0 2px 6px rgba(0,0,0,.15)}
  .twk-chip>span{position:absolute;top:0;bottom:0;right:0;width:34%;
    display:flex;flex-direction:column;box-shadow:-1px 0 0 rgba(0,0,0,.1)}
  .twk-chip>span>i{flex:1;box-shadow:0 -1px 0 rgba(0,0,0,.1)}
  .twk-chip>span>i:first-child{box-shadow:none}
  .twk-chip svg{position:absolute;top:6px;left:6px;width:13px;height:13px;
    filter:drop-shadow(0 1px 1px rgba(0,0,0,.3))}
`;

// ── useTweaks ───────────────────────────────────────────────────────────────
// Single source of truth for tweak values. setTweak persists via the host
// (__edit_mode_set_keys → host rewrites the EDITMODE block on disk).
function useTweaks(defaults) {
  const [values, setValues] = React.useState(defaults);
  // Accepts either setTweak('key', value) or setTweak({ key: value, ... }) so a
  // useState-style call doesn't write a "[object Object]" key into the persisted
  // JSON block.
  const setTweak = React.useCallback((keyOrEdits, val) => {
    const edits = typeof keyOrEdits === 'object' && keyOrEdits !== null ? keyOrEdits : {
      [keyOrEdits]: val
    };
    setValues(prev => ({
      ...prev,
      ...edits
    }));
    window.parent.postMessage({
      type: '__edit_mode_set_keys',
      edits
    }, '*');
    // Same-window signal so in-page listeners (deck-stage rail thumbnails)
    // can react — the parent message only reaches the host, not peers.
    window.dispatchEvent(new CustomEvent('tweakchange', {
      detail: edits
    }));
  }, []);
  return [values, setTweak];
}

// ── TweaksPanel ─────────────────────────────────────────────────────────────
// Floating shell. Registers the protocol listener BEFORE announcing
// availability — if the announce ran first, the host's activate could land
// before our handler exists and the toolbar toggle would silently no-op.
// The close button posts __edit_mode_dismissed so the host's toolbar toggle
// flips off in lockstep; the host echoes __deactivate_edit_mode back which
// is what actually hides the panel.
function TweaksPanel({
  title = 'Tweaks',
  children
}) {
  const [open, setOpen] = React.useState(false);
  const dragRef = React.useRef(null);
  const offsetRef = React.useRef({
    x: 16,
    y: 16
  });
  const PAD = 16;
  const clampToViewport = React.useCallback(() => {
    const panel = dragRef.current;
    if (!panel) return;
    const w = panel.offsetWidth,
      h = panel.offsetHeight;
    const maxRight = Math.max(PAD, window.innerWidth - w - PAD);
    const maxBottom = Math.max(PAD, window.innerHeight - h - PAD);
    offsetRef.current = {
      x: Math.min(maxRight, Math.max(PAD, offsetRef.current.x)),
      y: Math.min(maxBottom, Math.max(PAD, offsetRef.current.y))
    };
    panel.style.right = offsetRef.current.x + 'px';
    panel.style.bottom = offsetRef.current.y + 'px';
  }, []);
  React.useEffect(() => {
    if (!open) return;
    clampToViewport();
    if (typeof ResizeObserver === 'undefined') {
      window.addEventListener('resize', clampToViewport);
      return () => window.removeEventListener('resize', clampToViewport);
    }
    const ro = new ResizeObserver(clampToViewport);
    ro.observe(document.documentElement);
    return () => ro.disconnect();
  }, [open, clampToViewport]);
  React.useEffect(() => {
    const onMsg = e => {
      const t = e?.data?.type;
      if (t === '__activate_edit_mode') setOpen(true);else if (t === '__deactivate_edit_mode') setOpen(false);
    };
    window.addEventListener('message', onMsg);
    window.parent.postMessage({
      type: '__edit_mode_available'
    }, '*');
    return () => window.removeEventListener('message', onMsg);
  }, []);
  const dismiss = () => {
    setOpen(false);
    window.parent.postMessage({
      type: '__edit_mode_dismissed'
    }, '*');
  };
  const onDragStart = e => {
    const panel = dragRef.current;
    if (!panel) return;
    const r = panel.getBoundingClientRect();
    const sx = e.clientX,
      sy = e.clientY;
    const startRight = window.innerWidth - r.right;
    const startBottom = window.innerHeight - r.bottom;
    const move = ev => {
      offsetRef.current = {
        x: startRight - (ev.clientX - sx),
        y: startBottom - (ev.clientY - sy)
      };
      clampToViewport();
    };
    const up = () => {
      window.removeEventListener('mousemove', move);
      window.removeEventListener('mouseup', up);
    };
    window.addEventListener('mousemove', move);
    window.addEventListener('mouseup', up);
  };
  if (!open) return null;
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("style", null, __TWEAKS_STYLE), /*#__PURE__*/React.createElement("div", {
    ref: dragRef,
    className: "twk-panel",
    "data-omelette-chrome": "",
    style: {
      right: offsetRef.current.x,
      bottom: offsetRef.current.y
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-hd",
    onMouseDown: onDragStart
  }, /*#__PURE__*/React.createElement("b", null, title), /*#__PURE__*/React.createElement("button", {
    className: "twk-x",
    "aria-label": "Close tweaks",
    onMouseDown: e => e.stopPropagation(),
    onClick: dismiss
  }, "\u2715")), /*#__PURE__*/React.createElement("div", {
    className: "twk-body"
  }, children)));
}

// ── Layout helpers ──────────────────────────────────────────────────────────

function TweakSection({
  label,
  children
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "twk-sect"
  }, label), children);
}
function TweakRow({
  label,
  value,
  children,
  inline = false
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: inline ? 'twk-row twk-row-h' : 'twk-row'
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-lbl"
  }, /*#__PURE__*/React.createElement("span", null, label), value != null && /*#__PURE__*/React.createElement("span", {
    className: "twk-val"
  }, value)), children);
}

// ── Controls ────────────────────────────────────────────────────────────────

function TweakSlider({
  label,
  value,
  min = 0,
  max = 100,
  step = 1,
  unit = '',
  onChange
}) {
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label,
    value: `${value}${unit}`
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    className: "twk-slider",
    min: min,
    max: max,
    step: step,
    value: value,
    onChange: e => onChange(Number(e.target.value))
  }));
}
function TweakToggle({
  label,
  value,
  onChange
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "twk-row twk-row-h"
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-lbl"
  }, /*#__PURE__*/React.createElement("span", null, label)), /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "twk-toggle",
    "data-on": value ? '1' : '0',
    role: "switch",
    "aria-checked": !!value,
    onClick: () => onChange(!value)
  }, /*#__PURE__*/React.createElement("i", null)));
}
function TweakRadio({
  label,
  value,
  options,
  onChange
}) {
  const trackRef = React.useRef(null);
  const [dragging, setDragging] = React.useState(false);
  // The active value is read by pointer-move handlers attached for the lifetime
  // of a drag — ref it so a stale closure doesn't fire onChange for every move.
  const valueRef = React.useRef(value);
  valueRef.current = value;

  // Segments wrap mid-word once per-segment width runs out. The track is
  // ~248px (280 panel − 28 body pad − 4 seg pad), each button loses 12px
  // to its own padding, and 11.5px system-ui averages ~6.3px/char — so 2
  // options fit ~16 chars each, 3 fit ~10. Past that (or >3 options), fall
  // back to a dropdown rather than wrap.
  const labelLen = o => String(typeof o === 'object' ? o.label : o).length;
  const maxLen = options.reduce((m, o) => Math.max(m, labelLen(o)), 0);
  const fitsAsSegments = maxLen <= ({
    2: 16,
    3: 10
  }[options.length] ?? 0);
  if (!fitsAsSegments) {
    // <select> emits strings — map back to the original option value so the
    // fallback stays type-preserving (numbers, booleans) like the segment path.
    const resolve = s => {
      const m = options.find(o => String(typeof o === 'object' ? o.value : o) === s);
      return m === undefined ? s : typeof m === 'object' ? m.value : m;
    };
    return /*#__PURE__*/React.createElement(TweakSelect, {
      label: label,
      value: value,
      options: options,
      onChange: s => onChange(resolve(s))
    });
  }
  const opts = options.map(o => typeof o === 'object' ? o : {
    value: o,
    label: o
  });
  const idx = Math.max(0, opts.findIndex(o => o.value === value));
  const n = opts.length;
  const segAt = clientX => {
    const r = trackRef.current.getBoundingClientRect();
    const inner = r.width - 4;
    const i = Math.floor((clientX - r.left - 2) / inner * n);
    return opts[Math.max(0, Math.min(n - 1, i))].value;
  };
  const onPointerDown = e => {
    setDragging(true);
    const v0 = segAt(e.clientX);
    if (v0 !== valueRef.current) onChange(v0);
    const move = ev => {
      if (!trackRef.current) return;
      const v = segAt(ev.clientX);
      if (v !== valueRef.current) onChange(v);
    };
    const up = () => {
      setDragging(false);
      window.removeEventListener('pointermove', move);
      window.removeEventListener('pointerup', up);
    };
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
  };
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label
  }, /*#__PURE__*/React.createElement("div", {
    ref: trackRef,
    role: "radiogroup",
    onPointerDown: onPointerDown,
    className: dragging ? 'twk-seg dragging' : 'twk-seg'
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-seg-thumb",
    style: {
      left: `calc(2px + ${idx} * (100% - 4px) / ${n})`,
      width: `calc((100% - 4px) / ${n})`
    }
  }), opts.map(o => /*#__PURE__*/React.createElement("button", {
    key: o.value,
    type: "button",
    role: "radio",
    "aria-checked": o.value === value
  }, o.label))));
}
function TweakSelect({
  label,
  value,
  options,
  onChange
}) {
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label
  }, /*#__PURE__*/React.createElement("select", {
    className: "twk-field",
    value: value,
    onChange: e => onChange(e.target.value)
  }, options.map(o => {
    const v = typeof o === 'object' ? o.value : o;
    const l = typeof o === 'object' ? o.label : o;
    return /*#__PURE__*/React.createElement("option", {
      key: v,
      value: v
    }, l);
  })));
}
function TweakText({
  label,
  value,
  placeholder,
  onChange
}) {
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label
  }, /*#__PURE__*/React.createElement("input", {
    className: "twk-field",
    type: "text",
    value: value,
    placeholder: placeholder,
    onChange: e => onChange(e.target.value)
  }));
}
function TweakNumber({
  label,
  value,
  min,
  max,
  step = 1,
  unit = '',
  onChange
}) {
  const clamp = n => {
    if (min != null && n < min) return min;
    if (max != null && n > max) return max;
    return n;
  };
  const startRef = React.useRef({
    x: 0,
    val: 0
  });
  const onScrubStart = e => {
    e.preventDefault();
    startRef.current = {
      x: e.clientX,
      val: value
    };
    const decimals = (String(step).split('.')[1] || '').length;
    const move = ev => {
      const dx = ev.clientX - startRef.current.x;
      const raw = startRef.current.val + dx * step;
      const snapped = Math.round(raw / step) * step;
      onChange(clamp(Number(snapped.toFixed(decimals))));
    };
    const up = () => {
      window.removeEventListener('pointermove', move);
      window.removeEventListener('pointerup', up);
    };
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "twk-num"
  }, /*#__PURE__*/React.createElement("span", {
    className: "twk-num-lbl",
    onPointerDown: onScrubStart
  }, label), /*#__PURE__*/React.createElement("input", {
    type: "number",
    value: value,
    min: min,
    max: max,
    step: step,
    onChange: e => onChange(clamp(Number(e.target.value)))
  }), unit && /*#__PURE__*/React.createElement("span", {
    className: "twk-num-unit"
  }, unit));
}

// Relative-luminance contrast pick — checkmarks drawn over a swatch need to
// read on both #111 and #fafafa without per-option configuration. Hex input
// only (#rgb / #rrggbb); named or rgb()/hsl() colors fall through to "light".
function __twkIsLight(hex) {
  const h = String(hex).replace('#', '');
  const x = h.length === 3 ? h.replace(/./g, c => c + c) : h.padEnd(6, '0');
  const n = parseInt(x.slice(0, 6), 16);
  if (Number.isNaN(n)) return true;
  const r = n >> 16 & 255,
    g = n >> 8 & 255,
    b = n & 255;
  return r * 299 + g * 587 + b * 114 > 148000;
}
const __TwkCheck = ({
  light
}) => /*#__PURE__*/React.createElement("svg", {
  viewBox: "0 0 14 14",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("path", {
  d: "M3 7.2 5.8 10 11 4.2",
  fill: "none",
  strokeWidth: "2.2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  stroke: light ? 'rgba(0,0,0,.78)' : '#fff'
}));

// TweakColor — curated color/palette picker. Each option is either a single
// hex string or an array of 1-5 hex strings; the card adapts — a lone color
// renders solid, a palette renders colors[0] as the hero (left ~2/3) with the
// rest stacked in a sharp column on the right. onChange emits the
// option in the shape it was passed (string stays string, array stays array).
// Without options it falls back to the native color input for back-compat.
function TweakColor({
  label,
  value,
  options,
  onChange
}) {
  if (!options || !options.length) {
    return /*#__PURE__*/React.createElement("div", {
      className: "twk-row twk-row-h"
    }, /*#__PURE__*/React.createElement("div", {
      className: "twk-lbl"
    }, /*#__PURE__*/React.createElement("span", null, label)), /*#__PURE__*/React.createElement("input", {
      type: "color",
      className: "twk-swatch",
      value: value,
      onChange: e => onChange(e.target.value)
    }));
  }
  // Native <input type=color> emits lowercase hex per the HTML spec, so
  // compare case-insensitively. String() guards JSON.stringify(undefined),
  // which returns the primitive undefined (no .toLowerCase).
  const key = o => String(JSON.stringify(o)).toLowerCase();
  const cur = key(value);
  return /*#__PURE__*/React.createElement(TweakRow, {
    label: label
  }, /*#__PURE__*/React.createElement("div", {
    className: "twk-chips",
    role: "radiogroup"
  }, options.map((o, i) => {
    const colors = Array.isArray(o) ? o : [o];
    const [hero, ...rest] = colors;
    const sup = rest.slice(0, 4);
    const on = key(o) === cur;
    return /*#__PURE__*/React.createElement("button", {
      key: i,
      type: "button",
      className: "twk-chip",
      role: "radio",
      "aria-checked": on,
      "data-on": on ? '1' : '0',
      "aria-label": colors.join(', '),
      title: colors.join(' · '),
      style: {
        background: hero
      },
      onClick: () => onChange(o)
    }, sup.length > 0 && /*#__PURE__*/React.createElement("span", null, sup.map((c, j) => /*#__PURE__*/React.createElement("i", {
      key: j,
      style: {
        background: c
      }
    }))), on && /*#__PURE__*/React.createElement(__TwkCheck, {
      light: __twkIsLight(hero)
    }));
  })));
}
function TweakButton({
  label,
  onClick,
  secondary = false
}) {
  return /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: secondary ? 'twk-btn secondary' : 'twk-btn',
    onClick: onClick
  }, label);
}
Object.assign(window, {
  useTweaks,
  TweaksPanel,
  TweakSection,
  TweakRow,
  TweakSlider,
  TweakToggle,
  TweakRadio,
  TweakSelect,
  TweakText,
  TweakNumber,
  TweakColor,
  TweakButton
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "add-property/tweaks-panel.jsx", error: String((e && e.message) || e) }); }

// assets/filters.js
try { (() => {
/* RentCheck — Filtration module
   Owns the entire filtering experience for the results page:
   filter state, the listing predicate, sorting, the active-filter count,
   and building/wiring the filter sidebar UI.

   results.js drives rendering; it asks this module for the visible set:
     RCFilters.init({ listings, amenLabel, onChange });
     var visible = RCFilters.apply(listings);   // filtered + sorted
     var n       = RCFilters.activeCount();      // # of non-default filters
*/
(function () {
  'use strict';

  // popular filters + property types live here (filter-only concern)
  var POP_FILTERS = ['verif', 'deposit', 'checkin', 'parking', 'wifi', 'pets'];
  var TYPES = [{
    k: 'studio',
    l: 'Studio'
  }, {
    k: 'apartman',
    l: 'Apartman'
  }, {
    k: 'soba',
    l: 'Soba'
  }, {
    k: 'kuca',
    l: 'Kuća'
  }];
  var DEFAULTS = {
    maxPrice: 120,
    minScore: 80,
    minScoreFloor: 70
  };
  var filters = {
    pop: {},
    type: {},
    maxPrice: DEFAULTS.maxPrice,
    minScore: DEFAULTS.minScore,
    stay: 'all',
    sort: 'picks'
  };
  var cfg = null;
  function checkSvg() {
    return '<svg width="9" height="9" viewBox="0 0 12 12" fill="none"><path d="M1.5 6.5L4.5 9.5L10.5 3.5" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  }

  // ── predicate + sort ─────────────────────────────────────────
  function passes(l) {
    for (var k in filters.pop) if (filters.pop[k] && l.amen.indexOf(k) === -1) return false;
    var anyType = Object.keys(filters.type).some(function (t) {
      return filters.type[t];
    });
    if (anyType && !filters.type[l.type]) return false;
    if (l.price > filters.maxPrice) return false;
    if (l.hostScore < filters.minScore) return false;
    if (filters.stay !== 'all' && l.minNights > parseInt(filters.stay, 10)) return false;
    return true;
  }
  function sortList(arr) {
    var s = filters.sort;
    return arr.slice().sort(function (a, b) {
      if (s === 'price-asc') return a.price - b.price;
      if (s === 'price-desc') return b.price - a.price;
      if (s === 'score') return b.hostScore - a.hostScore;
      if (s === 'dist') return a.dist - b.dist;
      return b.score + b.reviews / 400 - (a.score + a.reviews / 400); // picks
    });
  }
  function activeFilterCount() {
    var n = 0;
    for (var k in filters.pop) if (filters.pop[k]) n++;
    for (var t in filters.type) if (filters.type[t]) n++;
    if (filters.maxPrice < DEFAULTS.maxPrice) n++;
    if (filters.minScore > DEFAULTS.minScore) n++;
    if (filters.stay !== 'all') n++;
    return n;
  }

  // ── UI ───────────────────────────────────────────────────────
  function buildUI() {
    var listings = cfg.listings,
      amenLabel = cfg.amenLabel,
      fire = cfg.onChange;
    var pop = document.getElementById('popFilters');
    if (pop) {
      pop.innerHTML = POP_FILTERS.map(function (k) {
        var cnt = listings.filter(function (l) {
          return l.amen.indexOf(k) !== -1;
        }).length;
        return '<label class="fcheck"><input type="checkbox" data-pop="' + k + '"><span class="box">' + checkSvg() + '</span>' + '<span class="lbl">' + amenLabel[k] + '</span><span class="cnt">' + cnt + '</span></label>';
      }).join('');
      pop.addEventListener('change', function (e) {
        var k = e.target.getAttribute('data-pop');
        if (k) {
          filters.pop[k] = e.target.checked;
          fire();
        }
      });
    }
    var tf = document.getElementById('typeFilters');
    if (tf) {
      tf.innerHTML = TYPES.map(function (t) {
        var cnt = listings.filter(function (l) {
          return l.type === t.k;
        }).length;
        if (!cnt) return '';
        return '<label class="fcheck"><input type="checkbox" data-type="' + t.k + '"><span class="box">' + checkSvg() + '</span>' + '<span class="lbl">' + t.l + '</span><span class="cnt">' + cnt + '</span></label>';
      }).join('');
      tf.addEventListener('change', function (e) {
        var k = e.target.getAttribute('data-type');
        if (k) {
          filters.type[k] = e.target.checked;
          fire();
        }
      });
    }
    var pr = document.getElementById('priceRange'),
      pv = document.getElementById('priceVal');
    if (pr) pr.addEventListener('input', function () {
      filters.maxPrice = +pr.value;
      if (pv) pv.textContent = pr.value + ' €';
      fire();
    });
    var sr = document.getElementById('scoreRange'),
      sv = document.getElementById('scoreVal');
    if (sr) sr.addEventListener('input', function () {
      filters.minScore = +sr.value;
      if (sv) sv.textContent = sr.value + '+';
      fire();
    });
    var chips = document.getElementById('stayChips');
    if (chips) chips.addEventListener('click', function (e) {
      var c = e.target.closest('.stay-chip');
      if (!c) return;
      chips.querySelectorAll('.stay-chip').forEach(function (x) {
        x.classList.toggle('active', x === c);
      });
      filters.stay = c.getAttribute('data-stay');
      fire();
    });
    var sortSel = document.getElementById('sortSelect');
    if (sortSel) sortSel.addEventListener('change', function (e) {
      filters.sort = e.target.value;
      fire();
    });
    var reset = document.getElementById('filterReset');
    if (reset) reset.addEventListener('click', function () {
      filters = {
        pop: {},
        type: {},
        maxPrice: DEFAULTS.maxPrice,
        minScore: DEFAULTS.minScore,
        stay: 'all',
        sort: filters.sort
      };
      if (pop) pop.querySelectorAll('input').forEach(function (i) {
        i.checked = false;
      });
      if (tf) tf.querySelectorAll('input').forEach(function (i) {
        i.checked = false;
      });
      if (pr) {
        pr.value = DEFAULTS.maxPrice;
        if (pv) pv.textContent = DEFAULTS.maxPrice + ' €';
      }
      if (sr) {
        sr.value = DEFAULTS.minScore;
        if (sv) sv.textContent = DEFAULTS.minScore + '+';
      }
      if (chips) chips.querySelectorAll('.stay-chip').forEach(function (x) {
        x.classList.toggle('active', x.getAttribute('data-stay') === 'all');
      });
      fire();
    });
  }

  // ── public API ───────────────────────────────────────────────
  window.RCFilters = {
    init: function (c) {
      cfg = c;
      buildUI();
    },
    apply: function (list) {
      return sortList(list.filter(passes));
    },
    activeCount: activeFilterCount,
    get state() {
      return filters;
    }
  };
})();
})(); } catch (e) { __ds_ns.__errors.push({ path: "assets/filters.js", error: String((e && e.message) || e) }); }

// assets/results.js
try { (() => {
/* RentCheck — Results page logic
   Listing data + filtering/sorting, Leaflet map with price markers,
   card↔marker hover sync, and live re-filter on the compact search bar. */
(function () {
  'use strict';

  var CENTER = [44.8167, 20.4670]; // Beograd centar

  var LISTINGS = [{
    id: 'l1',
    title: 'Svetli studio na Dorćolu',
    area: 'Dorćol',
    sqm: 38,
    price: 42,
    score: 94,
    reviews: 1424,
    stars: 0,
    type: 'studio',
    minNights: 1,
    lat: 44.8255,
    lng: 20.4680,
    host: 'Jovana K.',
    hostScore: 96,
    hostTier: 'Verifikovan domaćin',
    amen: ['verif', 'deposit', 'checkin', 'wifi'],
    img: 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=600&q=80',
    avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=80&q=80',
    desc: 'Renoviran studio u srcu Dorćola, na 5 minuta od Kalemegdana. Idealno za kratak gradski boravak.'
  }, {
    id: 'l2',
    title: 'Moderan apartman na Vračaru',
    area: 'Vračar',
    sqm: 48,
    price: 52,
    score: 92,
    reviews: 980,
    stars: 4,
    type: 'apartman',
    minNights: 3,
    lat: 44.7985,
    lng: 20.4762,
    host: 'Nikola V.',
    hostScore: 94,
    hostTier: 'Elite Partner',
    amen: ['verif', 'parking', 'wifi', 'desk'],
    img: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=600&q=80',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=80&q=80',
    desc: 'Prostran dvosoban apartman sa radnim kutkom — pogodan i za poslovne boravke do mesec dana.'
  }, {
    id: 'l3',
    title: 'Apartman za produženi boravak',
    area: 'Novi Beograd',
    sqm: 42,
    price: 33,
    score: 89,
    reviews: 540,
    stars: 0,
    type: 'apartman',
    minNights: 7,
    lat: 44.8230,
    lng: 20.4130,
    host: 'Marko B.',
    hostScore: 88,
    hostTier: 'Verifikovan domaćin',
    amen: ['deposit', 'wifi', 'laundry', 'pets'],
    img: 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80',
    avatar: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=80&q=80',
    desc: 'Mirna lokacija na Novom Beogradu sa veš mašinom i mesečnim popustom za boravke od 7+ noćenja.'
  }, {
    id: 'l4',
    title: 'Garsonjera kod Skadarlije',
    area: 'Stari grad',
    sqm: 28,
    price: 39,
    score: 88,
    reviews: 410,
    stars: 0,
    type: 'studio',
    minNights: 2,
    lat: 44.8180,
    lng: 20.4625,
    host: 'Ana M.',
    hostScore: 90,
    hostTier: 'Verifikovan domaćin',
    amen: ['verif', 'checkin', 'wifi'],
    img: 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=600&q=80',
    avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=80&q=80',
    desc: 'Šarmantna garsonjera nadomak boemske Skadarlije, sa self check-in pristupom u svako doba.'
  }, {
    id: 'l5',
    title: 'Dvosoban stan u Zemunu',
    area: 'Zemun',
    sqm: 56,
    price: 47,
    score: 91,
    reviews: 760,
    stars: 4,
    type: 'apartman',
    minNights: 3,
    lat: 44.8430,
    lng: 20.4110,
    host: 'Stefan J.',
    hostScore: 93,
    hostTier: 'Elite Partner',
    amen: ['verif', 'deposit', 'parking', 'wifi'],
    img: 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=600&q=80',
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=80&q=80',
    desc: 'Komforan stan uz Dunav sa privatnim parkingom — 12 minuta vožnje do centra grada.'
  }, {
    id: 'l6',
    title: 'Soba u centru, Savamala',
    area: 'Savamala',
    sqm: 18,
    price: 24,
    score: 85,
    reviews: 230,
    stars: 0,
    type: 'soba',
    minNights: 1,
    lat: 44.8120,
    lng: 20.4520,
    host: 'Petar L.',
    hostScore: 86,
    hostTier: 'Verifikovan domaćin',
    amen: ['checkin', 'wifi'],
    img: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=600&q=80',
    avatar: 'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?auto=format&fit=crop&w=80&q=80',
    desc: 'Povoljna privatna soba u umetničkoj četvrti Savamala, na koraku od splavova i Beograda na vodi.'
  }, {
    id: 'l7',
    title: 'Kuća sa baštom, Banovo brdo',
    area: 'Banovo brdo',
    sqm: 90,
    price: 78,
    score: 95,
    reviews: 320,
    stars: 4,
    type: 'kuca',
    minNights: 4,
    lat: 44.7870,
    lng: 20.4200,
    host: 'Milica R.',
    hostScore: 97,
    hostTier: 'Elite Partner',
    amen: ['verif', 'deposit', 'parking', 'wifi', 'pets', 'laundry'],
    img: 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=600&q=80',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=80&q=80',
    desc: 'Cela kuća sa baštom i parkingom, dozvoljeni ljubimci — savršeno za porodični boravak.'
  }, {
    id: 'l8',
    title: 'Penthaus sa terasom',
    area: 'Vračar',
    sqm: 70,
    price: 96,
    score: 93,
    reviews: 510,
    stars: 4,
    type: 'apartman',
    minNights: 3,
    lat: 44.8005,
    lng: 20.4785,
    host: 'Dušan T.',
    hostScore: 95,
    hostTier: 'Elite Partner',
    amen: ['verif', 'deposit', 'parking', 'wifi', 'desk'],
    img: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=600&q=80',
    avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=80&q=80',
    desc: 'Penthaus sa panoramskom terasom i pogledom na grad — luksuzni kratkoročni boravak na Vračaru.'
  }];
  var AMEN_LABEL = {
    verif: 'Verifikovan domaćin',
    deposit: 'Bez kaucije',
    checkin: 'Self check-in',
    parking: 'Parking',
    wifi: 'Wi-Fi',
    pets: 'Ljubimci OK',
    laundry: 'Veš mašina',
    desk: 'Radni kutak'
  };

  // ── helpers ──────────────────────────────────────────────────
  function haversine(a, b) {
    var R = 6371,
      dLat = (b[0] - a[0]) * Math.PI / 180,
      dLng = (b[1] - a[1]) * Math.PI / 180;
    var s = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(a[0] * Math.PI / 180) * Math.cos(b[0] * Math.PI / 180) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return R * 2 * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
  }
  LISTINGS.forEach(function (l) {
    l.dist = haversine(CENTER, [l.lat, l.lng]);
  });
  function scoreWord(s) {
    return s >= 92 ? 'Izuzetno' : s >= 85 ? 'Odlično' : s >= 78 ? 'Vrlo dobro' : 'Dobro';
  }
  function reviewsWord(n) {
    var m10 = n % 10,
      m100 = n % 100;
    if (m10 === 1 && m100 !== 11) return 'recenzija';
    if (m10 >= 2 && m10 <= 4 && (m100 < 12 || m100 > 14)) return 'recenzije';
    return 'recenzija';
  }
  function discountFor(n) {
    return n <= 3 ? 0 : n <= 13 ? 12 : 22;
  }

  // ── filtration lives in assets/filters.js (window.RCFilters) ──

  // ── card rendering ───────────────────────────────────────────
  var nights = 7,
    guests = 2;
  function starSvg() {
    return '<svg width="11" height="11" viewBox="0 0 12 12" fill="currentColor"><path d="M6 .8l1.5 3.5 3.7.3-2.8 2.5.9 3.6L6 9.3 2.7 11.2l.9-3.6L.8 5.1l3.7-.3z"/></svg>';
  }
  function checkSvg() {
    return '<svg width="9" height="9" viewBox="0 0 12 12" fill="none"><path d="M1.5 6.5L4.5 9.5L10.5 3.5" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  }
  function cardHtml(l) {
    var disc = discountFor(nights);
    var total = Math.round(l.price * nights * (1 - disc / 100) + 20);
    var stars = l.stars ? '<span class="rc-stars">' + Array(l.stars).fill(starSvg()).join('') + '</span>' : '';
    var feats = l.amen.slice(0, 4).map(function (a) {
      return '<span class="rc-feat">' + AMEN_LABEL[a] + '</span>';
    }).join('');
    return '' + '<article class="result-card" data-id="' + l.id + '">' + '<div class="rc-photo">' + '<img src="' + l.img + '" alt="' + l.title + '" loading="lazy" />' + '<span class="rc-verif">' + checkSvg() + ' Verifikovano</span>' + '<button class="rc-fav" type="button" aria-label="Sačuvaj"><svg width="15" height="15" viewBox="0 0 16 16" fill="none"><path d="M8 13.5S2 9.8 2 5.9C2 4.2 3.3 3 4.9 3c1 0 1.9.5 2.4 1.3l.7 1 .7-1C9.2 3.5 10.1 3 11.1 3 12.7 3 14 4.2 14 5.9c0 3.9-6 7.6-6 7.6Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg></button>' + '<span class="rc-daypill">min. ' + l.minNights + ' noćenja</span>' + '</div>' + '<div class="rc-body">' + '<div class="rc-main">' + '<div class="rc-title">' + l.title + stars + '</div>' + '<div class="rc-loc"><a href="#">' + l.area + '</a><span class="dist">' + l.dist.toFixed(1).replace('.', ',') + ' km od centra · ' + l.sqm + ' m²</span></div>' + '<div class="rc-feats">' + feats + '</div>' + '<p class="rc-desc">' + l.desc + '</p>' + '<div class="rc-host"><img src="' + l.avatar + '" alt="' + l.host + '" />' + '<div><div class="rc-host-name">' + l.host + ' <span class="v">✓</span></div>' + '<div class="rc-host-meta">' + l.hostTier + ' · ' + l.hostScore + '</div></div></div>' + '</div>' + '<div class="rc-side">' + '<div class="rc-score-row">' + '<div class="rc-score-word"><div class="w">' + scoreWord(l.score) + '</div><div class="n">' + l.reviews.toLocaleString('sr-RS') + ' ' + reviewsWord(l.reviews) + '</div></div>' + '<div class="rc-score-badge">' + (l.score / 10).toFixed(1).replace('.', ',') + '</div>' + '</div>' + '<div>' + '<div class="rc-price-wrap">' + '<div class="rc-price">' + l.price + ' €<small> / noć</small></div>' + '<div class="rc-price-sub">' + total.toLocaleString('sr-RS') + ' € ukupno · ' + nights + ' noćenja</div>' + (disc > 0 ? '<div class="rc-price-disc">−' + disc + '% za dužinu boravka</div>' : '') + '</div>' + '<button class="rc-select-btn" type="button">Izaberi datume →</button>' + '</div>' + '</div>' + '</div>' + '</article>';
  }
  var listEl = document.getElementById('resultsList');
  var noResults = document.getElementById('noResults');
  var headCount = document.getElementById('headCount');
  var appliedCount = document.getElementById('appliedCount');
  function activeFilterCount() {
    return window.RCFilters.activeCount();
  }
  function render() {
    var visible = window.RCFilters.apply(LISTINGS);
    listEl.innerHTML = visible.map(cardHtml).join('');
    headCount.textContent = visible.length;
    noResults.classList.toggle('show', visible.length === 0);
    var afc = activeFilterCount();
    appliedCount.textContent = afc ? afc + (afc === 1 ? ' aktivan filter' : ' aktivnih filtera') : '';
    wireCards();
    syncMarkers(visible);
  }

  // ── card ↔ marker interactions ──────────────────────────────
  function wireCards() {
    listEl.querySelectorAll('.result-card').forEach(function (card) {
      var id = card.getAttribute('data-id');
      card.addEventListener('mouseenter', function () {
        setMarkerActive(id, true);
      });
      card.addEventListener('mouseleave', function () {
        setMarkerActive(id, false);
      });
      var fav = card.querySelector('.rc-fav');
      if (fav) fav.addEventListener('click', function (e) {
        e.stopPropagation();
        fav.classList.toggle('on');
      });
    });
  }

  // ── map ──────────────────────────────────────────────────────
  var map,
    markers = {},
    tileLayer;
  var TILE = {
    light: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    dark: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
  };
  function curTheme() {
    return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  }
  function initMap() {
    if (typeof L === 'undefined') return;
    map = L.map('map', {
      zoomControl: true,
      scrollWheelZoom: false,
      attributionControl: false
    }).setView(CENTER, 12);
    tileLayer = L.tileLayer(TILE[curTheme()], {
      maxZoom: 19,
      subdomains: 'abcd'
    }).addTo(map);
    L.control.attribution({
      prefix: false
    }).addAttribution('© OpenStreetMap, © CARTO').addTo(map);
    LISTINGS.forEach(function (l) {
      var icon = L.divIcon({
        className: 'price-marker',
        html: '<span>' + l.price + ' €</span>',
        iconSize: [0, 0]
      });
      var m = L.marker([l.lat, l.lng], {
        icon: icon
      }).addTo(map);
      m.on('click', function () {
        focusCard(l.id);
      });
      m.on('mouseover', function () {
        setCardActive(l.id, true);
      });
      m.on('mouseout', function () {
        setCardActive(l.id, false);
      });
      markers[l.id] = m;
    });
    window.__setMapTheme = function (theme) {
      if (tileLayer) tileLayer.setUrl(TILE[theme] || TILE.light);
    };
    document.getElementById('mapFull').addEventListener('click', function () {
      map.scrollWheelZoom.enable();
      map.fitBounds(LISTINGS.map(function (l) {
        return [l.lat, l.lng];
      }), {
        padding: [30, 30]
      });
    });
  }
  function setMarkerActive(id, on) {
    var m = markers[id];
    if (!m) return;
    var el = m.getElement();
    if (el) el.classList.toggle('active', on);
  }
  function setCardActive(id, on) {
    var c = listEl.querySelector('.result-card[data-id="' + id + '"]');
    if (c) c.classList.toggle('active', on);
  }
  function focusCard(id) {
    var c = listEl.querySelector('.result-card[data-id="' + id + '"]');
    if (!c) return;
    listEl.querySelectorAll('.result-card.active').forEach(function (x) {
      x.classList.remove('active');
    });
    c.classList.add('active');
    window.scrollTo({
      top: c.getBoundingClientRect().top + window.pageYOffset - 185,
      behavior: 'smooth'
    });
  }
  function syncMarkers(visible) {
    var vis = {};
    visible.forEach(function (l) {
      vis[l.id] = true;
    });
    for (var id in markers) {
      var el = markers[id].getElement();
      if (el) el.style.display = vis[id] ? '' : 'none';
    }
  }

  // ── build filter UI ──────────────────────────────────────────
  // moved to assets/filters.js — RCFilters.init() builds + wires the sidebar.

  // view toggle
  document.getElementById('viewToggle').addEventListener('click', function (e) {
    var b = e.target.closest('button[data-view]');
    if (!b) return;
    document.querySelectorAll('#viewToggle button').forEach(function (x) {
      x.classList.toggle('active', x === b);
    });
    listEl.classList.toggle('grid', b.getAttribute('data-view') === 'grid');
    if (map) setTimeout(function () {
      map.invalidateSize();
    }, 50);
  });

  // ── hydrate heading from search state ────────────────────────
  function hydrateHeading() {
    if (!window.RCSearch) return;
    window.RCSearch.hydrateFromQuery();
    var st = window.RCSearch.state;
    nights = st.nights || 7;
    guests = st.guests || 2;
    var dest = st.dest || 'Beograd';
    document.getElementById('headDest').textContent = dest;
    document.getElementById('crumbCity').textContent = dest;
    var sub = document.getElementById('headSub');
    if (st.checkin && st.checkout) {
      sub.textContent = window.RCSearch.fmtDay(st.checkin) + ' – ' + window.RCSearch.fmtDay(st.checkout) + ' · ' + guests + ' ' + window.RCSearch.guestsWord(guests) + ' · ' + nights + ' ' + window.RCSearch.nightsWord(nights);
    } else {
      sub.textContent = 'Verifikovani kratkoročni stanovi · ' + nights + ' ' + window.RCSearch.nightsWord(nights) + ' · ' + guests + ' ' + window.RCSearch.guestsWord(guests);
    }
  }

  // re-filter live when the compact bar fires search
  window.addEventListener('rc:search', function (e) {
    var st = e.detail;
    nights = st.nights || nights;
    guests = st.guests || guests;
    var dest = st.dest || 'Beograd';
    document.getElementById('headDest').textContent = dest;
    document.getElementById('crumbCity').textContent = dest;
    hydrateHeading();
    render();
  });
  function boot() {
    hydrateHeading();
    window.RCFilters.init({
      listings: LISTINGS,
      amenLabel: AMEN_LABEL,
      onChange: render
    });
    render();
    initMap();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);else boot();
})();
})(); } catch (e) { __ds_ns.__errors.push({ path: "assets/results.js", error: String((e && e.message) || e) }); }

// assets/search-system.js
try { (() => {
/* RentCheck — Search System logic
   Powers the search bar, the flexible-date calendar, and the guests/stay popovers.
   Auto-initialises every .rc-searchbar on the page.
   - A bar with [data-navigate] sends the user to the results page on "Pretraži".
   - A .compact bar (results page) hydrates from the URL and emits a window 'rc:search'
     event instead of navigating, so the page can re-filter in place. */
(function () {
  'use strict';

  var RESULTS_PAGE = 'RentCheck Rezultati Pretrage.html';
  var TODAY = new Date(2026, 5, 10); // anchored: 10 June 2026
  TODAY.setHours(0, 0, 0, 0);
  var MONTHS = ['januar', 'februar', 'mart', 'april', 'maj', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar'];
  var MONTHS_SHORT = ['jan', 'feb', 'mar', 'apr', 'maj', 'jun', 'jul', 'avg', 'sep', 'okt', 'nov', 'dec'];
  var DOW = ['Po', 'Ut', 'Sr', 'Če', 'Pe', 'Su', 'Ne'];
  var CITIES = [{
    name: 'Beograd',
    meta: 'Srbija · 2.140 stanova',
    q: 'beograd'
  }, {
    name: 'Novi Sad',
    meta: 'Vojvodina · 870 stanova',
    q: 'novi sad'
  }, {
    name: 'Zlatibor',
    meta: 'Zapadna Srbija · 1.260 stanova',
    q: 'zlatibor'
  }, {
    name: 'Niš',
    meta: 'Južna Srbija · 410 stanova',
    q: 'niš'
  }, {
    name: 'Kopaonik',
    meta: 'Planina · 540 stanova',
    q: 'kopaonik'
  }, {
    name: 'Vrnjačka Banja',
    meta: 'Banja · 320 stanova',
    q: 'vrnjačka banja'
  }];

  // ── shared state ─────────────────────────────────────────────
  var state = {
    dest: '',
    checkin: null,
    // Date or null
    checkout: null,
    // Date or null
    flex: 0,
    // 0 = exact, or ±N days
    guests: 2,
    nights: 7
  };
  function nightsWord(n) {
    var m10 = n % 10,
      m100 = n % 100;
    if (m10 === 1 && m100 !== 11) return 'noćenje';
    return 'noćenja';
  }
  function guestsWord(n) {
    var m10 = n % 10,
      m100 = n % 100;
    if (m10 === 1 && m100 !== 11) return 'gost';
    if (m10 >= 2 && m10 <= 4 && (m100 < 12 || m100 > 14)) return 'gosta';
    return 'gostiju';
  }
  function tierFor(n) {
    if (n <= 3) return {
      name: 'Vikend i kratki boravak',
      pct: 0
    };
    if (n <= 13) return {
      name: 'Nedeljni boravak',
      pct: 12
    };
    return {
      name: 'Produženi boravak',
      pct: 22
    };
  }
  function fmtDay(d) {
    return d.getDate() + '. ' + MONTHS_SHORT[d.getMonth()];
  }
  function iso(d) {
    return d ? d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() : '';
  }
  function parseIso(s) {
    if (!s) return null;
    var p = s.split('-');
    if (p.length !== 3) return null;
    var d = new Date(+p[0], +p[1] - 1, +p[2]);
    d.setHours(0, 0, 0, 0);
    return d;
  }
  function addDays(d, n) {
    var x = new Date(d);
    x.setDate(x.getDate() + n);
    return x;
  }
  function diffDays(a, b) {
    return Math.round((b - a) / 86400000);
  }
  function sameDay(a, b) {
    return a && b && a.getTime() === b.getTime();
  }

  // ── label rendering ──────────────────────────────────────────
  function datesLabel() {
    if (state.checkin && state.checkout) {
      var base = fmtDay(state.checkin) + ' – ' + fmtDay(state.checkout);
      return state.flex ? base + ' · ±' + state.flex + ' d' : base;
    }
    if (state.checkin) return fmtDay(state.checkin) + ' – …';
    return '';
  }
  function guestsLabel() {
    return state.guests + ' ' + guestsWord(state.guests) + ' · ' + state.nights + ' ' + nightsWord(state.nights);
  }
  function syncLabels(bar) {
    var dv = bar.querySelector('.rc-dates-val');
    if (dv) {
      var dl = datesLabel();
      dv.textContent = dl || 'Datum dolaska — odlaska';
      dv.classList.toggle('placeholder', !dl);
    }
    var gv = bar.querySelector('.rc-guests-val');
    if (gv) gv.textContent = guestsLabel();
    var di = bar.querySelector('.rc-dest-input');
    if (di && di.value !== state.dest) di.value = state.dest;
    var df = bar.querySelector('.rc-field-dest');
    if (df) df.classList.toggle('has-value', !!state.dest);
  }

  // ── popover plumbing ─────────────────────────────────────────
  var openPop = null,
    openAnchor = null;
  function placePop(pop, anchor) {
    var r = anchor.getBoundingClientRect();
    pop.style.position = 'fixed';
    pop.style.top = r.bottom + 10 + 'px';
    var w = pop.offsetWidth || 600;
    var left = r.left;
    if (left + w > window.innerWidth - 12) left = Math.max(12, window.innerWidth - w - 12);
    pop.style.left = left + 'px';
  }
  function closePop() {
    if (openPop) {
      openPop.classList.remove('open');
      openPop = null;
    }
    if (openAnchor) {
      openAnchor.classList.remove('active');
      openAnchor = null;
    }
  }
  function showPop(pop, anchor) {
    if (openPop === pop) {
      closePop();
      return;
    }
    closePop();
    pop.classList.add('open');
    placePop(pop, anchor);
    anchor.classList.add('active');
    openPop = pop;
    openAnchor = anchor;
  }
  document.addEventListener('click', function (e) {
    if (openPop && !openPop.contains(e.target) && (!openAnchor || !openAnchor.contains(e.target))) closePop();
  });
  window.addEventListener('resize', function () {
    if (openPop && openAnchor) placePop(openPop, openAnchor);
  });
  window.addEventListener('scroll', function () {
    if (openPop && openAnchor) placePop(openPop, openAnchor);
  }, true);

  // ── calendar ─────────────────────────────────────────────────
  var calBaseMonth = new Date(TODAY.getFullYear(), TODAY.getMonth(), 1);
  function buildCalendar(onChange) {
    var pop = document.createElement('div');
    pop.className = 'rc-pop rc-cal';
    pop.innerHTML = '<div class="rc-cal-tabs">' + '<button class="rc-cal-tab active" data-tab="cal">Kalendar</button>' + '<button class="rc-cal-tab" data-tab="flex">Fleksibilno</button>' + '</div>' + '<div class="rc-cal-pane" data-pane="cal">' + '<div class="rc-months">' + '<button class="rc-cal-nav prev" aria-label="Prethodni">' + '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' + '<button class="rc-cal-nav next" aria-label="Sledeći">' + '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' + '<div class="rc-month" data-m="0"></div>' + '<div class="rc-month" data-m="1"></div>' + '</div>' + '</div>' + '<div class="rc-cal-pane" data-pane="flex" hidden>' + '<div class="rc-flexpane-q">Koliko dugo želite da ostanete?</div>' + '<div class="rc-flex-grid">' + '<div class="rc-flex-card" data-nights="2"><div class="fc-ico"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="3" y="4" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M3 8h14M7 2v3M13 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></div><div class="fc-t">Vikend</div><div class="fc-s">2–3 noćenja</div></div>' + '<div class="rc-flex-card active" data-nights="7"><div class="fc-ico"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="3" y="4" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M3 8h14M7 2v3M13 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></div><div class="fc-t">Nedelja</div><div class="fc-s">7 noćenja</div></div>' + '<div class="rc-flex-card" data-nights="14"><div class="fc-ico"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="3" y="4" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M3 8h14M7 2v3M13 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></div><div class="fc-t">Dve nedelje</div><div class="fc-s">14 noćenja</div></div>' + '<div class="rc-flex-card" data-nights="30"><div class="fc-ico"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="3" y="4" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M3 8h14M7 2v3M13 2v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></div><div class="fc-t">Mesec</div><div class="fc-s">30 noćenja</div></div>' + '</div>' + '</div>' + '<div class="rc-flex-row">' + '<span style="font-size:12px;font-weight:600;color:var(--ink3);margin-right:4px">Tolerancija datuma:</span>' + '<button class="rc-flex-chip active" data-flex="0">Tačni datumi</button>' + '<button class="rc-flex-chip" data-flex="1">± 1 dan</button>' + '<button class="rc-flex-chip" data-flex="2">± 2 dana</button>' + '<button class="rc-flex-chip" data-flex="3">± 3 dana</button>' + '<button class="rc-flex-chip" data-flex="7">± 7 dana</button>' + '</div>';
    document.body.appendChild(pop);
    function renderMonth(slot, monthDate) {
      var y = monthDate.getFullYear(),
        m = monthDate.getMonth();
      var first = new Date(y, m, 1);
      var startDow = (first.getDay() + 6) % 7; // Monday-first
      var daysIn = new Date(y, m + 1, 0).getDate();
      var html = '<div class="rc-month-head">' + MONTHS[m] + ' ' + y + '</div>' + '<div class="rc-dow">' + DOW.map(function (d) {
        return '<span>' + d + '</span>';
      }).join('') + '</div>' + '<div class="rc-grid">';
      for (var i = 0; i < startDow; i++) html += '<div class="rc-day empty"></div>';
      for (var d = 1; d <= daysIn; d++) {
        var cur = new Date(y, m, d);
        cur.setHours(0, 0, 0, 0);
        var cls = 'rc-day';
        if (cur < TODAY) cls += ' muted';
        if (sameDay(cur, TODAY)) cls += ' today';
        if (state.checkin && state.checkout && cur > state.checkin && cur < state.checkout) cls += ' in-range';
        if (sameDay(cur, state.checkin)) cls += ' range-start';
        if (sameDay(cur, state.checkout)) cls += ' range-end';
        html += '<div class="' + cls + '" data-d="' + iso(cur) + '">' + d + '</div>';
      }
      html += '</div>';
      slot.innerHTML = html;
    }
    function renderCal() {
      var slots = pop.querySelectorAll('.rc-month');
      renderMonth(slots[0], calBaseMonth);
      renderMonth(slots[1], new Date(calBaseMonth.getFullYear(), calBaseMonth.getMonth() + 1, 1));
    }
    pop.addEventListener('click', function (e) {
      var tab = e.target.closest('.rc-cal-tab');
      if (tab) {
        pop.querySelectorAll('.rc-cal-tab').forEach(function (t) {
          t.classList.toggle('active', t === tab);
        });
        pop.querySelectorAll('.rc-cal-pane').forEach(function (p) {
          p.hidden = p.getAttribute('data-pane') !== tab.getAttribute('data-tab');
        });
        if (openAnchor) placePop(pop, openAnchor);
        return;
      }
      var nav = e.target.closest('.rc-cal-nav');
      if (nav) {
        var delta = nav.classList.contains('prev') ? -1 : 1;
        var nb = new Date(calBaseMonth.getFullYear(), calBaseMonth.getMonth() + delta, 1);
        var floor = new Date(TODAY.getFullYear(), TODAY.getMonth(), 1);
        if (nb < floor) nb = floor;
        calBaseMonth = nb;
        renderCal();
        return;
      }
      var day = e.target.closest('.rc-day');
      if (day && !day.classList.contains('muted') && !day.classList.contains('empty')) {
        var picked = parseIso(day.getAttribute('data-d'));
        if (!state.checkin || state.checkin && state.checkout || picked < state.checkin) {
          state.checkin = picked;
          state.checkout = null;
        } else if (sameDay(picked, state.checkin)) {
          return;
        } else {
          state.checkout = picked;
          state.nights = Math.max(1, Math.min(30, diffDays(state.checkin, state.checkout)));
        }
        renderCal();
        onChange();
        return;
      }
      var chip = e.target.closest('.rc-flex-chip');
      if (chip) {
        pop.querySelectorAll('.rc-flex-chip').forEach(function (c) {
          c.classList.toggle('active', c === chip);
        });
        state.flex = parseInt(chip.getAttribute('data-flex'), 10);
        onChange();
        return;
      }
      var fcard = e.target.closest('.rc-flex-card');
      if (fcard) {
        pop.querySelectorAll('.rc-flex-card').forEach(function (c) {
          c.classList.toggle('active', c === fcard);
        });
        state.nights = parseInt(fcard.getAttribute('data-nights'), 10);
        if (state.checkin) {
          state.checkout = addDays(state.checkin, state.nights);
          renderCal();
        }
        onChange();
        return;
      }
    });
    renderCal();
    pop._render = renderCal;
    return pop;
  }

  // ── guests / stay ────────────────────────────────────────────
  function buildGuests(onChange) {
    var pop = document.createElement('div');
    pop.className = 'rc-pop rc-guests';
    pop.innerHTML = '<div class="rc-step-row">' + '<div><div class="rc-step-title">Gosti</div><div class="rc-step-sub">Osobe koje borave u stanu</div></div>' + '<div class="rc-stepper"><button class="rc-step-btn" data-g="-1" aria-label="Manje">−</button>' + '<span class="rc-step-val" data-gval>2</span>' + '<button class="rc-step-btn" data-g="1" aria-label="Više">+</button></div>' + '</div>' + '<div class="rc-step-row">' + '<div><div class="rc-step-title">Dužina boravka</div><div class="rc-step-sub">Od 1 do 30 noćenja</div></div>' + '<div class="rc-stepper"><button class="rc-step-btn" data-n="-1" aria-label="Manje">−</button>' + '<span class="rc-step-val" data-nval>7</span>' + '<button class="rc-step-btn" data-n="1" aria-label="Više">+</button></div>' + '</div>' + '<div class="rc-stay-tier"><span class="dot"></span><span data-tier></span></div>' + '<div class="rc-guests-foot"><button class="rc-apply">Gotovo</button></div>';
    document.body.appendChild(pop);
    function render() {
      pop.querySelector('[data-gval]').textContent = state.guests;
      pop.querySelector('[data-nval]').textContent = state.nights;
      var t = tierFor(state.nights);
      pop.querySelector('[data-tier]').textContent = t.pct > 0 ? t.name + ' · ' + t.pct + '% niža cena' : t.name + ' · puna cena';
      pop.querySelector('[data-g="-1"]').disabled = state.guests <= 1;
      pop.querySelector('[data-g="1"]').disabled = state.guests >= 16;
      pop.querySelector('[data-n="-1"]').disabled = state.nights <= 1;
      pop.querySelector('[data-n="1"]').disabled = state.nights >= 30;
    }
    pop.addEventListener('click', function (e) {
      var g = e.target.closest('[data-g]');
      if (g) {
        state.guests = Math.max(1, Math.min(16, state.guests + parseInt(g.getAttribute('data-g'), 10)));
        render();
        onChange();
        return;
      }
      var n = e.target.closest('[data-n]');
      if (n) {
        state.nights = Math.max(1, Math.min(30, state.nights + parseInt(n.getAttribute('data-n'), 10)));
        if (state.checkin) state.checkout = addDays(state.checkin, state.nights);
        render();
        onChange();
        return;
      }
      if (e.target.closest('.rc-apply')) closePop();
    });
    render();
    pop._render = render;
    return pop;
  }

  // ── destination suggest ──────────────────────────────────────
  function buildSuggest(bar, onChange) {
    var field = bar.querySelector('.rc-field-dest');
    var pop = document.createElement('div');
    pop.className = 'rc-pop rc-suggest';
    function list(filter) {
      var f = (filter || '').toLowerCase().trim();
      var items = CITIES.filter(function (c) {
        return !f || c.name.toLowerCase().indexOf(f) === 0 || c.q.indexOf(f) === 0;
      });
      if (!items.length) items = CITIES;
      pop.innerHTML = '<div class="rc-suggest-head">Popularne destinacije</div>' + items.map(function (c) {
        return '<div class="rc-sugg" data-name="' + c.name + '">' + '<div class="rc-sugg-pin"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1.5c-2.8 0-5 2.2-5 5C3 10.5 8 14.5 8 14.5S13 10.5 13 6.5c0-2.8-2.2-5-5-5Z" stroke="currentColor" stroke-width="1.4"/><circle cx="8" cy="6.5" r="1.6" fill="currentColor"/></svg></div>' + '<div><div class="rc-sugg-name">' + c.name + '</div><div class="rc-sugg-meta">' + c.meta + '</div></div></div>';
      }).join('');
    }
    document.body.appendChild(pop);
    var input = bar.querySelector('.rc-dest-input');
    function open() {
      list(input.value);
      showPop(pop, field);
    }
    input.addEventListener('focus', open);
    input.addEventListener('input', function () {
      state.dest = input.value;
      field.classList.toggle('has-value', !!input.value);
      list(input.value);
      if (openPop !== pop) showPop(pop, field);
    });
    pop.addEventListener('click', function (e) {
      var s = e.target.closest('.rc-sugg');
      if (s) {
        state.dest = s.getAttribute('data-name');
        input.value = state.dest;
        field.classList.add('has-value');
        closePop();
        onChange();
      }
    });
    var clear = bar.querySelector('.rc-clear');
    if (clear) clear.addEventListener('click', function (e) {
      e.stopPropagation();
      state.dest = '';
      input.value = '';
      field.classList.remove('has-value');
      input.focus();
      list('');
    });
    return pop;
  }

  // ── query string ─────────────────────────────────────────────
  function buildQuery() {
    var p = new URLSearchParams();
    if (state.dest) p.set('dest', state.dest);
    if (state.checkin) p.set('in', iso(state.checkin));
    if (state.checkout) p.set('out', iso(state.checkout));
    if (state.flex) p.set('flex', state.flex);
    p.set('guests', state.guests);
    p.set('nights', state.nights);
    return p.toString();
  }
  function hydrateFromQuery() {
    var p = new URLSearchParams(location.search);
    if (p.get('dest')) state.dest = p.get('dest');
    if (p.get('in')) state.checkin = parseIso(p.get('in'));
    if (p.get('out')) state.checkout = parseIso(p.get('out'));
    if (p.get('flex')) state.flex = parseInt(p.get('flex'), 10) || 0;
    if (p.get('guests')) state.guests = parseInt(p.get('guests'), 10) || 2;
    if (p.get('nights')) state.nights = parseInt(p.get('nights'), 10) || 7;
    if (state.checkin) calBaseMonth = new Date(state.checkin.getFullYear(), state.checkin.getMonth(), 1);
  }

  // ── init each bar ────────────────────────────────────────────
  function initBar(bar) {
    var navigate = bar.hasAttribute('data-navigate');
    var calPop, guestsPop;
    function onChange() {
      syncLabels(bar);
      if (calPop && calPop._render) calPop._render();
      if (guestsPop && guestsPop._render) guestsPop._render();
    }
    buildSuggest(bar, onChange);
    calPop = buildCalendar(onChange);
    guestsPop = buildGuests(onChange);
    var datesTrigger = bar.querySelector('.rc-field-dates');
    if (datesTrigger) datesTrigger.addEventListener('click', function (e) {
      if (e.target.closest('.rc-dest-input')) return;
      showPop(calPop, datesTrigger);
    });
    var guestsTrigger = bar.querySelector('.rc-field-guests');
    if (guestsTrigger) guestsTrigger.addEventListener('click', function () {
      showPop(guestsPop, guestsTrigger);
    });
    var btn = bar.querySelector('.rc-search-btn');
    if (btn) btn.addEventListener('click', function () {
      closePop();
      if (navigate) {
        location.href = RESULTS_PAGE + '?' + buildQuery();
      } else {
        window.dispatchEvent(new CustomEvent('rc:search', {
          detail: Object.assign({}, state)
        }));
        // keep URL in sync without reload
        try {
          history.replaceState(null, '', location.pathname + '?' + buildQuery());
        } catch (e) {}
      }
    });
    syncLabels(bar);
  }
  function initAll() {
    var bars = document.querySelectorAll('.rc-searchbar');
    if (!bars.length) return;
    // results page (compact) hydrates from the URL before labels render
    if (document.querySelector('.rc-searchbar.compact')) hydrateFromQuery();
    bars.forEach(initBar);
  }

  // expose for the results page
  window.RCSearch = {
    get state() {
      return state;
    },
    hydrateFromQuery: hydrateFromQuery,
    nightsWord: nightsWord,
    guestsWord: guestsWord,
    tierFor: tierFor,
    fmtDay: fmtDay
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAll);else initAll();
})();
})(); } catch (e) { __ds_ns.__errors.push({ path: "assets/search-system.js", error: String((e && e.message) || e) }); }

// design-canvas.jsx
try { (() => {
// DesignCanvas.jsx — Figma-ish design canvas wrapper
// Warm gray grid bg + Sections + Artboards + PostIt notes.
// Artboards are reorderable (grip-drag), labels/titles are inline-editable,
// and any artboard can be opened in a fullscreen focus overlay (←/→/Esc).
// State persists to a .design-canvas.state.json sidecar via the host
// bridge. No assets, no deps.
//
// Usage:
//   <DesignCanvas>
//     <DCSection id="onboarding" title="Onboarding" subtitle="First-run variants">
//       <DCArtboard id="a" label="A · Dusk" width={260} height={480}>…</DCArtboard>
//       <DCArtboard id="b" label="B · Minimal" width={260} height={480}>…</DCArtboard>
//     </DCSection>
//   </DesignCanvas>

const DC = {
  bg: '#f0eee9',
  grid: 'rgba(0,0,0,0.06)',
  label: 'rgba(60,50,40,0.7)',
  title: 'rgba(40,30,20,0.85)',
  subtitle: 'rgba(60,50,40,0.6)',
  postitBg: '#fef4a8',
  postitText: '#5a4a2a',
  font: '-apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif'
};

// One-time CSS injection (classes are dc-prefixed so they don't collide with
// the hosted design's own styles).
if (typeof document !== 'undefined' && !document.getElementById('dc-styles')) {
  const s = document.createElement('style');
  s.id = 'dc-styles';
  s.textContent = ['.dc-editable{cursor:text;outline:none;white-space:nowrap;border-radius:3px;padding:0 2px;margin:0 -2px}', '.dc-editable:focus{background:#fff;box-shadow:0 0 0 1.5px #c96442}', '[data-dc-slot]{transition:transform .18s cubic-bezier(.2,.7,.3,1)}', '[data-dc-slot].dc-dragging{transition:none;z-index:10;pointer-events:none}', '[data-dc-slot].dc-dragging .dc-card{box-shadow:0 12px 40px rgba(0,0,0,.25),0 0 0 2px #c96442;transform:scale(1.02)}', '.dc-card{transition:box-shadow .15s,transform .15s}', '.dc-card *{scrollbar-width:none}', '.dc-card *::-webkit-scrollbar{display:none}', '.dc-labelrow{display:flex;align-items:center;gap:4px;height:24px}', '.dc-grip{cursor:grab;display:flex;align-items:center;padding:5px 4px;border-radius:4px;transition:background .12s}', '.dc-grip:hover{background:rgba(0,0,0,.08)}', '.dc-grip:active{cursor:grabbing}', '.dc-labeltext{cursor:pointer;border-radius:4px;padding:3px 6px;display:flex;align-items:center;transition:background .12s}', '.dc-labeltext:hover{background:rgba(0,0,0,.05)}', '.dc-expand{position:absolute;bottom:100%;right:0;margin-bottom:5px;z-index:2;opacity:0;transition:opacity .12s,background .12s;', '  width:22px;height:22px;border-radius:5px;border:none;cursor:pointer;padding:0;', '  background:transparent;color:rgba(60,50,40,.7);display:flex;align-items:center;justify-content:center}', '.dc-expand:hover{background:rgba(0,0,0,.06);color:#2a251f}', '[data-dc-slot]:hover .dc-expand{opacity:1}'].join('\n');
  document.head.appendChild(s);
}
const DCCtx = React.createContext(null);

// ─────────────────────────────────────────────────────────────
// DesignCanvas — stateful wrapper around the pan/zoom viewport.
// Owns runtime state (per-section order, renamed titles/labels, focused
// artboard). Order/titles/labels persist to a .design-canvas.state.json
// sidecar next to the HTML. Reads go via plain fetch() so the saved
// arrangement is visible anywhere the HTML + sidecar are served together
// (omelette preview, direct link, downloaded zip). Writes go through the
// host's window.omelette bridge — editing requires the omelette runtime.
// Focus is ephemeral.
// ─────────────────────────────────────────────────────────────
const DC_STATE_FILE = '.design-canvas.state.json';
function DesignCanvas({
  children,
  minScale,
  maxScale,
  style
}) {
  const [state, setState] = React.useState({
    sections: {},
    focus: null
  });
  // Hold rendering until the sidecar read settles so the saved order/titles
  // appear on first paint (no source-order flash). didRead gates writes until
  // the read settles so the empty initial state can't clobber a slow read;
  // skipNextWrite suppresses the one echo-write that would otherwise follow
  // hydration.
  const [ready, setReady] = React.useState(false);
  const didRead = React.useRef(false);
  const skipNextWrite = React.useRef(false);
  React.useEffect(() => {
    let off = false;
    fetch('./' + DC_STATE_FILE).then(r => r.ok ? r.json() : null).then(saved => {
      if (off || !saved || !saved.sections) return;
      skipNextWrite.current = true;
      setState(s => ({
        ...s,
        sections: saved.sections
      }));
    }).catch(() => {}).finally(() => {
      didRead.current = true;
      if (!off) setReady(true);
    });
    const t = setTimeout(() => {
      if (!off) setReady(true);
    }, 150);
    return () => {
      off = true;
      clearTimeout(t);
    };
  }, []);
  React.useEffect(() => {
    if (!didRead.current) return;
    if (skipNextWrite.current) {
      skipNextWrite.current = false;
      return;
    }
    const t = setTimeout(() => {
      window.omelette?.writeFile(DC_STATE_FILE, JSON.stringify({
        sections: state.sections
      })).catch(() => {});
    }, 250);
    return () => clearTimeout(t);
  }, [state.sections]);

  // Build registries synchronously from children so FocusOverlay can read
  // them in the same render. Only direct DCSection > DCArtboard children are
  // walked — wrapping them in other elements opts out of focus/reorder.
  const registry = {}; // slotId -> { sectionId, artboard }
  const sectionMeta = {}; // sectionId -> { title, subtitle, slotIds[] }
  const sectionOrder = [];
  React.Children.forEach(children, sec => {
    if (!sec || sec.type !== DCSection) return;
    const sid = sec.props.id ?? sec.props.title;
    if (!sid) return;
    sectionOrder.push(sid);
    const persisted = state.sections[sid] || {};
    const srcIds = [];
    React.Children.forEach(sec.props.children, ab => {
      if (!ab || ab.type !== DCArtboard) return;
      const aid = ab.props.id ?? ab.props.label;
      if (!aid) return;
      registry[`${sid}/${aid}`] = {
        sectionId: sid,
        artboard: ab
      };
      srcIds.push(aid);
    });
    const kept = (persisted.order || []).filter(k => srcIds.includes(k));
    sectionMeta[sid] = {
      title: persisted.title ?? sec.props.title,
      subtitle: sec.props.subtitle,
      slotIds: [...kept, ...srcIds.filter(k => !kept.includes(k))]
    };
  });
  const api = React.useMemo(() => ({
    state,
    section: id => state.sections[id] || {},
    patchSection: (id, p) => setState(s => ({
      ...s,
      sections: {
        ...s.sections,
        [id]: {
          ...s.sections[id],
          ...(typeof p === 'function' ? p(s.sections[id] || {}) : p)
        }
      }
    })),
    setFocus: slotId => setState(s => ({
      ...s,
      focus: slotId
    }))
  }), [state]);

  // Esc exits focus; any outside pointerdown commits an in-progress rename.
  React.useEffect(() => {
    const onKey = e => {
      if (e.key === 'Escape') api.setFocus(null);
    };
    const onPd = e => {
      const ae = document.activeElement;
      if (ae && ae.isContentEditable && !ae.contains(e.target)) ae.blur();
    };
    document.addEventListener('keydown', onKey);
    document.addEventListener('pointerdown', onPd, true);
    return () => {
      document.removeEventListener('keydown', onKey);
      document.removeEventListener('pointerdown', onPd, true);
    };
  }, [api]);
  return /*#__PURE__*/React.createElement(DCCtx.Provider, {
    value: api
  }, /*#__PURE__*/React.createElement(DCViewport, {
    minScale: minScale,
    maxScale: maxScale,
    style: style
  }, ready && children), state.focus && registry[state.focus] && /*#__PURE__*/React.createElement(DCFocusOverlay, {
    entry: registry[state.focus],
    sectionMeta: sectionMeta,
    sectionOrder: sectionOrder
  }));
}

// ─────────────────────────────────────────────────────────────
// DCViewport — transform-based pan/zoom (internal)
//
// Input mapping (Figma-style):
//   • trackpad pinch  → zoom   (ctrlKey wheel; Safari gesture* events)
//   • trackpad scroll → pan    (two-finger)
//   • mouse wheel     → zoom   (notched; distinguished from trackpad scroll)
//   • middle-drag / primary-drag-on-bg → pan
//
// Transform state lives in a ref and is written straight to the DOM
// (translate3d + will-change) so wheel ticks don't go through React —
// keeps pans at 60fps on dense canvases.
// ─────────────────────────────────────────────────────────────
function DCViewport({
  children,
  minScale = 0.1,
  maxScale = 8,
  style = {}
}) {
  const vpRef = React.useRef(null);
  const worldRef = React.useRef(null);
  const tf = React.useRef({
    x: 0,
    y: 0,
    scale: 1
  });
  const apply = React.useCallback(() => {
    const {
      x,
      y,
      scale
    } = tf.current;
    const el = worldRef.current;
    if (el) el.style.transform = `translate3d(${x}px, ${y}px, 0) scale(${scale})`;
  }, []);
  React.useEffect(() => {
    const vp = vpRef.current;
    if (!vp) return;
    const zoomAt = (cx, cy, factor) => {
      const r = vp.getBoundingClientRect();
      const px = cx - r.left,
        py = cy - r.top;
      const t = tf.current;
      const next = Math.min(maxScale, Math.max(minScale, t.scale * factor));
      const k = next / t.scale;
      // keep the world point under the cursor fixed
      t.x = px - (px - t.x) * k;
      t.y = py - (py - t.y) * k;
      t.scale = next;
      apply();
    };

    // Mouse-wheel vs trackpad-scroll heuristic. A physical wheel sends
    // line-mode deltas (Firefox) or large integer pixel deltas with no X
    // component (Chrome/Safari, typically multiples of 100/120). Trackpad
    // two-finger scroll sends small/fractional pixel deltas, often with
    // non-zero deltaX. ctrlKey is set by the browser for trackpad pinch.
    const isMouseWheel = e => e.deltaMode !== 0 || e.deltaX === 0 && Number.isInteger(e.deltaY) && Math.abs(e.deltaY) >= 40;
    const onWheel = e => {
      e.preventDefault();
      if (isGesturing) return; // Safari: gesture* owns the pinch — discard concurrent wheels
      if (e.ctrlKey) {
        // trackpad pinch (or explicit ctrl+wheel)
        zoomAt(e.clientX, e.clientY, Math.exp(-e.deltaY * 0.01));
      } else if (isMouseWheel(e)) {
        // notched mouse wheel — fixed-ratio step per click
        zoomAt(e.clientX, e.clientY, Math.exp(-Math.sign(e.deltaY) * 0.18));
      } else {
        // trackpad two-finger scroll — pan
        tf.current.x -= e.deltaX;
        tf.current.y -= e.deltaY;
        apply();
      }
    };

    // Safari sends native gesture* events for trackpad pinch with a smooth
    // e.scale; preferring these over the ctrl+wheel fallback gives a much
    // better feel there. No-ops on other browsers. Safari also fires
    // ctrlKey wheel events during the same pinch — isGesturing makes
    // onWheel drop those entirely so they neither zoom nor pan.
    let gsBase = 1;
    let isGesturing = false;
    const onGestureStart = e => {
      e.preventDefault();
      isGesturing = true;
      gsBase = tf.current.scale;
    };
    const onGestureChange = e => {
      e.preventDefault();
      zoomAt(e.clientX, e.clientY, gsBase * e.scale / tf.current.scale);
    };
    const onGestureEnd = e => {
      e.preventDefault();
      isGesturing = false;
    };

    // Drag-pan: middle button anywhere, or primary button on canvas
    // background (anything that isn't an artboard or an inline editor).
    let drag = null;
    const onPointerDown = e => {
      const onBg = !e.target.closest('[data-dc-slot], .dc-editable');
      if (!(e.button === 1 || e.button === 0 && onBg)) return;
      e.preventDefault();
      vp.setPointerCapture(e.pointerId);
      drag = {
        id: e.pointerId,
        lx: e.clientX,
        ly: e.clientY
      };
      vp.style.cursor = 'grabbing';
    };
    const onPointerMove = e => {
      if (!drag || e.pointerId !== drag.id) return;
      tf.current.x += e.clientX - drag.lx;
      tf.current.y += e.clientY - drag.ly;
      drag.lx = e.clientX;
      drag.ly = e.clientY;
      apply();
    };
    const onPointerUp = e => {
      if (!drag || e.pointerId !== drag.id) return;
      vp.releasePointerCapture(e.pointerId);
      drag = null;
      vp.style.cursor = '';
    };
    vp.addEventListener('wheel', onWheel, {
      passive: false
    });
    vp.addEventListener('gesturestart', onGestureStart, {
      passive: false
    });
    vp.addEventListener('gesturechange', onGestureChange, {
      passive: false
    });
    vp.addEventListener('gestureend', onGestureEnd, {
      passive: false
    });
    vp.addEventListener('pointerdown', onPointerDown);
    vp.addEventListener('pointermove', onPointerMove);
    vp.addEventListener('pointerup', onPointerUp);
    vp.addEventListener('pointercancel', onPointerUp);
    return () => {
      vp.removeEventListener('wheel', onWheel);
      vp.removeEventListener('gesturestart', onGestureStart);
      vp.removeEventListener('gesturechange', onGestureChange);
      vp.removeEventListener('gestureend', onGestureEnd);
      vp.removeEventListener('pointerdown', onPointerDown);
      vp.removeEventListener('pointermove', onPointerMove);
      vp.removeEventListener('pointerup', onPointerUp);
      vp.removeEventListener('pointercancel', onPointerUp);
    };
  }, [apply, minScale, maxScale]);
  const gridSvg = `url("data:image/svg+xml,%3Csvg width='120' height='120' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M120 0H0v120' fill='none' stroke='${encodeURIComponent(DC.grid)}' stroke-width='1'/%3E%3C/svg%3E")`;
  return /*#__PURE__*/React.createElement("div", {
    ref: vpRef,
    className: "design-canvas",
    style: {
      height: '100vh',
      width: '100vw',
      background: DC.bg,
      overflow: 'hidden',
      overscrollBehavior: 'none',
      touchAction: 'none',
      position: 'relative',
      fontFamily: DC.font,
      boxSizing: 'border-box',
      ...style
    }
  }, /*#__PURE__*/React.createElement("div", {
    ref: worldRef,
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      transformOrigin: '0 0',
      willChange: 'transform',
      width: 'max-content',
      minWidth: '100%',
      minHeight: '100%',
      padding: '60px 0 80px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      inset: -6000,
      backgroundImage: gridSvg,
      backgroundSize: '120px 120px',
      pointerEvents: 'none',
      zIndex: -1
    }
  }), children));
}

// ─────────────────────────────────────────────────────────────
// DCSection — editable title + h-row of artboards in persisted order
// ─────────────────────────────────────────────────────────────
function DCSection({
  id,
  title,
  subtitle,
  children,
  gap = 48
}) {
  const ctx = React.useContext(DCCtx);
  const sid = id ?? title;
  const all = React.Children.toArray(children);
  const artboards = all.filter(c => c && c.type === DCArtboard);
  const rest = all.filter(c => !(c && c.type === DCArtboard));
  const srcOrder = artboards.map(a => a.props.id ?? a.props.label);
  const sec = ctx && sid && ctx.section(sid) || {};
  const order = React.useMemo(() => {
    const kept = (sec.order || []).filter(k => srcOrder.includes(k));
    return [...kept, ...srcOrder.filter(k => !kept.includes(k))];
  }, [sec.order, srcOrder.join('|')]);
  const byId = Object.fromEntries(artboards.map(a => [a.props.id ?? a.props.label, a]));
  return /*#__PURE__*/React.createElement("div", {
    "data-dc-section": sid,
    style: {
      marginBottom: 80,
      position: 'relative'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '0 60px 56px'
    }
  }, /*#__PURE__*/React.createElement(DCEditable, {
    tag: "div",
    value: sec.title ?? title,
    onChange: v => ctx && sid && ctx.patchSection(sid, {
      title: v
    }),
    style: {
      fontSize: 28,
      fontWeight: 600,
      color: DC.title,
      letterSpacing: -0.4,
      marginBottom: 6,
      display: 'inline-block'
    }
  }), subtitle && /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: 16,
      color: DC.subtitle
    }
  }, subtitle)), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap,
      padding: '0 60px',
      alignItems: 'flex-start',
      width: 'max-content'
    }
  }, order.map(k => /*#__PURE__*/React.createElement(DCArtboardFrame, {
    key: k,
    sectionId: sid,
    artboard: byId[k],
    order: order,
    label: (sec.labels || {})[k] ?? byId[k].props.label,
    onRename: v => ctx && ctx.patchSection(sid, x => ({
      labels: {
        ...x.labels,
        [k]: v
      }
    })),
    onReorder: next => ctx && ctx.patchSection(sid, {
      order: next
    }),
    onFocus: () => ctx && ctx.setFocus(`${sid}/${k}`)
  }))), rest);
}

// DCArtboard — marker; rendered by DCArtboardFrame via DCSection.
function DCArtboard() {
  return null;
}
function DCArtboardFrame({
  sectionId,
  artboard,
  label,
  order,
  onRename,
  onReorder,
  onFocus
}) {
  const {
    id: rawId,
    label: rawLabel,
    width = 260,
    height = 480,
    children,
    style = {}
  } = artboard.props;
  const id = rawId ?? rawLabel;
  const ref = React.useRef(null);

  // Live drag-reorder: dragged card sticks to cursor; siblings slide into
  // their would-be slots in real time via transforms. DOM order only
  // changes on drop.
  const onGripDown = e => {
    e.preventDefault();
    e.stopPropagation();
    const me = ref.current;
    // translateX is applied in local (pre-scale) space but pointer deltas and
    // getBoundingClientRect().left are screen-space — divide by the viewport's
    // current scale so the dragged card tracks the cursor at any zoom level.
    const scale = me.getBoundingClientRect().width / me.offsetWidth || 1;
    const peers = Array.from(document.querySelectorAll(`[data-dc-section="${sectionId}"] [data-dc-slot]`));
    const homes = peers.map(el => ({
      el,
      id: el.dataset.dcSlot,
      x: el.getBoundingClientRect().left
    }));
    const slotXs = homes.map(h => h.x);
    const startIdx = order.indexOf(id);
    const startX = e.clientX;
    let liveOrder = order.slice();
    me.classList.add('dc-dragging');
    const layout = () => {
      for (const h of homes) {
        if (h.id === id) continue;
        const slot = liveOrder.indexOf(h.id);
        h.el.style.transform = `translateX(${(slotXs[slot] - h.x) / scale}px)`;
      }
    };
    const move = ev => {
      const dx = ev.clientX - startX;
      me.style.transform = `translateX(${dx / scale}px)`;
      const cur = homes[startIdx].x + dx;
      let nearest = 0,
        best = Infinity;
      for (let i = 0; i < slotXs.length; i++) {
        const d = Math.abs(slotXs[i] - cur);
        if (d < best) {
          best = d;
          nearest = i;
        }
      }
      if (liveOrder.indexOf(id) !== nearest) {
        liveOrder = order.filter(k => k !== id);
        liveOrder.splice(nearest, 0, id);
        layout();
      }
    };
    const up = () => {
      document.removeEventListener('pointermove', move);
      document.removeEventListener('pointerup', up);
      const finalSlot = liveOrder.indexOf(id);
      me.classList.remove('dc-dragging');
      me.style.transform = `translateX(${(slotXs[finalSlot] - homes[startIdx].x) / scale}px)`;
      // After the settle transition, kill transitions + clear transforms +
      // commit the reorder in the same frame so there's no visual snap-back.
      setTimeout(() => {
        for (const h of homes) {
          h.el.style.transition = 'none';
          h.el.style.transform = '';
        }
        if (liveOrder.join('|') !== order.join('|')) onReorder(liveOrder);
        requestAnimationFrame(() => requestAnimationFrame(() => {
          for (const h of homes) h.el.style.transition = '';
        }));
      }, 180);
    };
    document.addEventListener('pointermove', move);
    document.addEventListener('pointerup', up);
  };
  return /*#__PURE__*/React.createElement("div", {
    ref: ref,
    "data-dc-slot": id,
    style: {
      position: 'relative',
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "dc-labelrow",
    style: {
      position: 'absolute',
      bottom: '100%',
      left: -4,
      marginBottom: 4,
      color: DC.label
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "dc-grip",
    onPointerDown: onGripDown,
    title: "Drag to reorder"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "9",
    height: "13",
    viewBox: "0 0 9 13",
    fill: "currentColor"
  }, /*#__PURE__*/React.createElement("circle", {
    cx: "2",
    cy: "2",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "7",
    cy: "2",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "2",
    cy: "6.5",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "7",
    cy: "6.5",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "2",
    cy: "11",
    r: "1.1"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "7",
    cy: "11",
    r: "1.1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "dc-labeltext",
    onClick: onFocus,
    title: "Click to focus"
  }, /*#__PURE__*/React.createElement(DCEditable, {
    value: label,
    onChange: onRename,
    onClick: e => e.stopPropagation(),
    style: {
      fontSize: 15,
      fontWeight: 500,
      color: DC.label,
      lineHeight: 1
    }
  }))), /*#__PURE__*/React.createElement("button", {
    className: "dc-expand",
    onClick: onFocus,
    onPointerDown: e => e.stopPropagation(),
    title: "Focus"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "12",
    height: "12",
    viewBox: "0 0 12 12",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "1.6",
    strokeLinecap: "round"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M7 1h4v4M5 11H1V7M11 1L7.5 4.5M1 11l3.5-3.5"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "dc-card",
    style: {
      borderRadius: 2,
      boxShadow: '0 1px 3px rgba(0,0,0,.08),0 4px 16px rgba(0,0,0,.06)',
      overflow: 'hidden',
      width,
      height,
      background: '#fff',
      ...style
    }
  }, children || /*#__PURE__*/React.createElement("div", {
    style: {
      height: '100%',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: '#bbb',
      fontSize: 13,
      fontFamily: DC.font
    }
  }, id)));
}

// Inline rename — commits on blur or Enter.
function DCEditable({
  value,
  onChange,
  style,
  tag = 'span',
  onClick
}) {
  const T = tag;
  return /*#__PURE__*/React.createElement(T, {
    className: "dc-editable",
    contentEditable: true,
    suppressContentEditableWarning: true,
    onClick: onClick,
    onPointerDown: e => e.stopPropagation(),
    onBlur: e => onChange && onChange(e.currentTarget.textContent),
    onKeyDown: e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        e.currentTarget.blur();
      }
    },
    style: style
  }, value);
}

// ─────────────────────────────────────────────────────────────
// Focus mode — overlay one artboard; ←/→ within section, ↑/↓ across
// sections, Esc or backdrop click to exit.
// ─────────────────────────────────────────────────────────────
function DCFocusOverlay({
  entry,
  sectionMeta,
  sectionOrder
}) {
  const ctx = React.useContext(DCCtx);
  const {
    sectionId,
    artboard
  } = entry;
  const sec = ctx.section(sectionId);
  const meta = sectionMeta[sectionId];
  const peers = meta.slotIds;
  const aid = artboard.props.id ?? artboard.props.label;
  const idx = peers.indexOf(aid);
  const secIdx = sectionOrder.indexOf(sectionId);
  const go = d => {
    const n = peers[(idx + d + peers.length) % peers.length];
    if (n) ctx.setFocus(`${sectionId}/${n}`);
  };
  const goSection = d => {
    const ns = sectionOrder[(secIdx + d + sectionOrder.length) % sectionOrder.length];
    const first = sectionMeta[ns] && sectionMeta[ns].slotIds[0];
    if (first) ctx.setFocus(`${ns}/${first}`);
  };
  React.useEffect(() => {
    const k = e => {
      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        go(-1);
      }
      if (e.key === 'ArrowRight') {
        e.preventDefault();
        go(1);
      }
      if (e.key === 'ArrowUp') {
        e.preventDefault();
        goSection(-1);
      }
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        goSection(1);
      }
    };
    document.addEventListener('keydown', k);
    return () => document.removeEventListener('keydown', k);
  });
  const {
    width = 260,
    height = 480,
    children
  } = artboard.props;
  const [vp, setVp] = React.useState({
    w: window.innerWidth,
    h: window.innerHeight
  });
  React.useEffect(() => {
    const r = () => setVp({
      w: window.innerWidth,
      h: window.innerHeight
    });
    window.addEventListener('resize', r);
    return () => window.removeEventListener('resize', r);
  }, []);
  const scale = Math.max(0.1, Math.min((vp.w - 200) / width, (vp.h - 260) / height, 2));
  const [ddOpen, setDd] = React.useState(false);
  const Arrow = ({
    dir,
    onClick
  }) => /*#__PURE__*/React.createElement("button", {
    onClick: e => {
      e.stopPropagation();
      onClick();
    },
    style: {
      position: 'absolute',
      top: '50%',
      [dir]: 28,
      transform: 'translateY(-50%)',
      border: 'none',
      background: 'rgba(255,255,255,.08)',
      color: 'rgba(255,255,255,.9)',
      width: 44,
      height: 44,
      borderRadius: 22,
      fontSize: 18,
      cursor: 'pointer',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      transition: 'background .15s'
    },
    onMouseEnter: e => e.currentTarget.style.background = 'rgba(255,255,255,.18)',
    onMouseLeave: e => e.currentTarget.style.background = 'rgba(255,255,255,.08)'
  }, /*#__PURE__*/React.createElement("svg", {
    width: "18",
    height: "18",
    viewBox: "0 0 18 18",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2",
    strokeLinecap: "round"
  }, /*#__PURE__*/React.createElement("path", {
    d: dir === 'left' ? 'M11 3L5 9l6 6' : 'M7 3l6 6-6 6'
  })));

  // Portal to body so position:fixed is the real viewport regardless of any
  // transform on DesignCanvas's ancestors (including the canvas zoom itself).
  return ReactDOM.createPortal(/*#__PURE__*/React.createElement("div", {
    onClick: () => ctx.setFocus(null),
    onWheel: e => e.preventDefault(),
    style: {
      position: 'fixed',
      inset: 0,
      zIndex: 100,
      background: 'rgba(24,20,16,.6)',
      backdropFilter: 'blur(14px)',
      fontFamily: DC.font,
      color: '#fff'
    }
  }, /*#__PURE__*/React.createElement("div", {
    onClick: e => e.stopPropagation(),
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      right: 0,
      height: 72,
      display: 'flex',
      alignItems: 'flex-start',
      padding: '16px 20px 0',
      gap: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'relative'
    }
  }, /*#__PURE__*/React.createElement("button", {
    onClick: () => setDd(o => !o),
    style: {
      border: 'none',
      background: 'transparent',
      color: '#fff',
      cursor: 'pointer',
      padding: '6px 8px',
      borderRadius: 6,
      textAlign: 'left',
      fontFamily: 'inherit'
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 8
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 18,
      fontWeight: 600,
      letterSpacing: -0.3
    }
  }, meta.title), /*#__PURE__*/React.createElement("svg", {
    width: "11",
    height: "11",
    viewBox: "0 0 11 11",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "1.8",
    strokeLinecap: "round",
    style: {
      opacity: .7
    }
  }, /*#__PURE__*/React.createElement("path", {
    d: "M2 4l3.5 3.5L9 4"
  }))), meta.subtitle && /*#__PURE__*/React.createElement("span", {
    style: {
      display: 'block',
      fontSize: 13,
      opacity: .6,
      fontWeight: 400,
      marginTop: 2
    }
  }, meta.subtitle)), ddOpen && /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: '100%',
      left: 0,
      marginTop: 4,
      background: '#2a251f',
      borderRadius: 8,
      boxShadow: '0 8px 32px rgba(0,0,0,.4)',
      padding: 4,
      minWidth: 200,
      zIndex: 10
    }
  }, sectionOrder.map(sid => /*#__PURE__*/React.createElement("button", {
    key: sid,
    onClick: () => {
      setDd(false);
      const f = sectionMeta[sid].slotIds[0];
      if (f) ctx.setFocus(`${sid}/${f}`);
    },
    style: {
      display: 'block',
      width: '100%',
      textAlign: 'left',
      border: 'none',
      cursor: 'pointer',
      background: sid === sectionId ? 'rgba(255,255,255,.1)' : 'transparent',
      color: '#fff',
      padding: '8px 12px',
      borderRadius: 5,
      fontSize: 14,
      fontWeight: sid === sectionId ? 600 : 400,
      fontFamily: 'inherit'
    }
  }, sectionMeta[sid].title)))), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1
    }
  }), /*#__PURE__*/React.createElement("button", {
    onClick: () => ctx.setFocus(null),
    onMouseEnter: e => e.currentTarget.style.background = 'rgba(255,255,255,.12)',
    onMouseLeave: e => e.currentTarget.style.background = 'transparent',
    style: {
      border: 'none',
      background: 'transparent',
      color: 'rgba(255,255,255,.7)',
      width: 32,
      height: 32,
      borderRadius: 16,
      fontSize: 20,
      cursor: 'pointer',
      lineHeight: 1,
      transition: 'background .12s'
    }
  }, "\xD7")), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 64,
      bottom: 56,
      left: 100,
      right: 100,
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      justifyContent: 'center',
      gap: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    onClick: e => e.stopPropagation(),
    style: {
      width: width * scale,
      height: height * scale,
      position: 'relative'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width,
      height,
      transform: `scale(${scale})`,
      transformOrigin: 'top left',
      background: '#fff',
      borderRadius: 2,
      overflow: 'hidden',
      boxShadow: '0 20px 80px rgba(0,0,0,.4)'
    }
  }, children || /*#__PURE__*/React.createElement("div", {
    style: {
      height: '100%',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: '#bbb'
    }
  }, aid))), /*#__PURE__*/React.createElement("div", {
    onClick: e => e.stopPropagation(),
    style: {
      fontSize: 14,
      fontWeight: 500,
      opacity: .85,
      textAlign: 'center'
    }
  }, (sec.labels || {})[aid] ?? artboard.props.label, /*#__PURE__*/React.createElement("span", {
    style: {
      opacity: .5,
      marginLeft: 10,
      fontVariantNumeric: 'tabular-nums'
    }
  }, idx + 1, " / ", peers.length))), /*#__PURE__*/React.createElement(Arrow, {
    dir: "left",
    onClick: () => go(-1)
  }), /*#__PURE__*/React.createElement(Arrow, {
    dir: "right",
    onClick: () => go(1)
  }), /*#__PURE__*/React.createElement("div", {
    onClick: e => e.stopPropagation(),
    style: {
      position: 'absolute',
      bottom: 20,
      left: '50%',
      transform: 'translateX(-50%)',
      display: 'flex',
      gap: 8
    }
  }, peers.map((p, i) => /*#__PURE__*/React.createElement("button", {
    key: p,
    onClick: () => ctx.setFocus(`${sectionId}/${p}`),
    style: {
      border: 'none',
      padding: 0,
      cursor: 'pointer',
      width: 6,
      height: 6,
      borderRadius: 3,
      background: i === idx ? '#fff' : 'rgba(255,255,255,.3)'
    }
  })))), document.body);
}

// ─────────────────────────────────────────────────────────────
// Post-it — absolute-positioned sticky note
// ─────────────────────────────────────────────────────────────
function DCPostIt({
  children,
  top,
  left,
  right,
  bottom,
  rotate = -2,
  width = 180
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top,
      left,
      right,
      bottom,
      width,
      background: DC.postitBg,
      padding: '14px 16px',
      fontFamily: '"Comic Sans MS", "Marker Felt", "Segoe Print", cursive',
      fontSize: 14,
      lineHeight: 1.4,
      color: DC.postitText,
      boxShadow: '0 2px 8px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08)',
      transform: `rotate(${rotate}deg)`,
      zIndex: 5
    }
  }, children);
}
Object.assign(window, {
  DesignCanvas,
  DCSection,
  DCArtboard,
  DCPostIt
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "design-canvas.jsx", error: String((e && e.message) || e) }); }

// ui_kits/rentcheck/Components.jsx
try { (() => {
// RentCheck — Shared UI Components
// Navbar, Button, Badge, TrustRing, ScoreBar, Card

const {
  useState
} = React;

// ── Button ────────────────────────────────────────────────────────────────
function RCButton({
  variant = 'primary',
  size = 'md',
  children,
  onClick,
  style,
  disabled
}) {
  const sizes = {
    sm: {
      h: 28,
      px: 12,
      fs: 12
    },
    md: {
      h: 36,
      px: 16,
      fs: 14
    },
    lg: {
      h: 44,
      px: 24,
      fs: 15
    }
  };
  const variants = {
    primary: {
      bg: '#0F6E56',
      color: '#EDF2F7',
      border: 'none',
      hover: '#1D9E75'
    },
    secondary: {
      bg: '#1A2D47',
      color: '#EDF2F7',
      border: '0.5px solid rgba(46,66,104,0.8)',
      hover: '#243656'
    },
    ghost: {
      bg: 'transparent',
      color: '#8DA4BE',
      border: 'none',
      hover: 'rgba(26,45,71,0.6)'
    },
    outline: {
      bg: 'transparent',
      color: '#9FE1CB',
      border: '1px solid #0F6E56',
      hover: 'rgba(15,110,86,0.12)'
    },
    danger: {
      bg: '#A32D2D',
      color: '#EDF2F7',
      border: 'none',
      hover: '#E24B4A'
    }
  };
  const s = sizes[size];
  const v = variants[variant];
  const [hov, setHov] = useState(false);
  return /*#__PURE__*/React.createElement("button", {
    onClick: onClick,
    disabled: disabled,
    onMouseEnter: () => setHov(true),
    onMouseLeave: () => setHov(false),
    style: {
      display: 'inline-flex',
      alignItems: 'center',
      justifyContent: 'center',
      gap: 6,
      height: s.h,
      padding: `0 ${s.px}px`,
      fontSize: s.fs,
      fontWeight: 500,
      fontFamily: RC.sans,
      borderRadius: 8,
      border: v.border || 'none',
      cursor: disabled ? 'not-allowed' : 'pointer',
      background: hov && !disabled ? v.hover : v.bg,
      color: v.color,
      opacity: disabled ? 0.4 : 1,
      transition: `background ${RC.fast}`,
      whiteSpace: 'nowrap',
      ...style
    }
  }, children);
}

// ── Badge ────────────────────────────────────────────────────────────────
function RCBadge({
  type,
  value,
  size = 'md'
}) {
  const map = {
    ...RC.verificationBadges,
    ...RC.reviewStatus
  };
  const styles = map[value] || {
    bg: '#1a1a18',
    text: '#888780',
    label: value
  };
  const isVerified = ['id_verified', 'gold', 'silver'].includes(value);
  const pad = size === 'sm' ? '2px 8px' : '3px 10px';
  const fs = size === 'sm' ? 10 : 11;
  return /*#__PURE__*/React.createElement("span", {
    style: {
      display: 'inline-flex',
      alignItems: 'center',
      gap: 3,
      background: styles.bg,
      color: styles.text,
      padding: pad,
      fontSize: fs,
      fontWeight: 500,
      borderRadius: 9999
    }
  }, isVerified && /*#__PURE__*/React.createElement("svg", {
    width: "8",
    height: "8",
    viewBox: "0 0 10 10",
    fill: "none"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M2 5l2 2 4-4",
    stroke: styles.text,
    strokeWidth: "1.5",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  })), styles.label);
}

// ── Trust Score Ring ─────────────────────────────────────────────────────
function TrustRing({
  score,
  size = 'md'
}) {
  const dims = {
    sm: {
      dim: 44,
      r: 17,
      cx: 22,
      sw: 4,
      fs: 11
    },
    md: {
      dim: 64,
      r: 26,
      cx: 32,
      sw: 5,
      fs: 14
    },
    lg: {
      dim: 88,
      r: 36,
      cx: 44,
      sw: 6,
      fs: 18
    }
  };
  const {
    dim,
    r,
    cx,
    sw,
    fs
  } = dims[size];
  const circ = 2 * Math.PI * r;
  const offset = circ - score / 100 * circ;
  const {
    stroke,
    text
  } = RC.getTrust(score);
  return /*#__PURE__*/React.createElement("svg", {
    width: dim,
    height: dim,
    viewBox: `0 0 ${dim} ${dim}`
  }, /*#__PURE__*/React.createElement("circle", {
    cx: cx,
    cy: cx,
    r: r,
    fill: "none",
    stroke: "#1A2D47",
    strokeWidth: sw
  }), /*#__PURE__*/React.createElement("circle", {
    cx: cx,
    cy: cx,
    r: r,
    fill: "none",
    stroke: stroke,
    strokeWidth: sw,
    strokeDasharray: circ,
    strokeDashoffset: offset,
    strokeLinecap: "round",
    transform: `rotate(-90 ${cx} ${cx})`,
    style: {
      transition: 'stroke-dashoffset 0.8s cubic-bezier(0.16,1,0.3,1)'
    }
  }), /*#__PURE__*/React.createElement("text", {
    x: cx,
    y: cx + 1,
    textAnchor: "middle",
    dominantBaseline: "middle",
    fontSize: fs,
    fontWeight: "500",
    fill: text,
    fontFamily: "DM Sans, sans-serif"
  }, score));
}

// ── Score Bar ────────────────────────────────────────────────────────────
function ScoreBar({
  label,
  value
}) {
  const color = value >= 80 ? '#1D9E75' : value >= 60 ? '#639922' : value >= 40 ? '#EF9F27' : '#E24B4A';
  const textColor = value >= 80 ? '#9FE1CB' : value >= 60 ? '#C0DD97' : value >= 40 ? '#FAC775' : '#F09595';
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      fontSize: 12
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      width: 130,
      color: RC.inkSecondary,
      flexShrink: 0
    }
  }, label), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      height: 4,
      background: '#2E4268',
      borderRadius: 9999,
      overflow: 'hidden'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width: `${value}%`,
      height: '100%',
      background: color,
      borderRadius: 9999,
      transition: 'width 0.7s cubic-bezier(0.16,1,0.3,1)'
    }
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      width: 24,
      textAlign: 'right',
      fontSize: 11,
      color: textColor,
      fontFamily: RC.mono
    }
  }, value));
}

// ── Card ─────────────────────────────────────────────────────────────────
function RCCard({
  children,
  variant = 'raised',
  style
}) {
  const bg = variant === 'elevated' ? RC.elevated : RC.raised;
  return /*#__PURE__*/React.createElement("div", {
    style: {
      background: bg,
      borderRadius: 12,
      border: RC.border,
      boxShadow: '0 1px 3px rgba(0,0,0,0.4)',
      ...style
    }
  }, children);
}

// ── Navbar ────────────────────────────────────────────────────────────────
function RCNavbar({
  onNavigate,
  currentScreen
}) {
  const links = [{
    label: 'Pretraži stanove',
    screen: 'marketplace'
  }, {
    label: 'Mapa',
    screen: 'map'
  }, {
    label: 'Kako funkcioniše',
    screen: null
  }];
  return /*#__PURE__*/React.createElement("header", {
    style: {
      position: 'sticky',
      top: 0,
      zIndex: 30,
      background: 'rgba(10,22,40,0.85)',
      backdropFilter: 'blur(12px)',
      borderBottom: RC.border
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: 1280,
      margin: '0 auto',
      padding: '0 24px',
      height: 60,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between'
    }
  }, /*#__PURE__*/React.createElement("button", {
    onClick: () => onNavigate('landing'),
    style: {
      background: 'none',
      border: 'none',
      cursor: 'pointer',
      fontSize: 17,
      fontWeight: 500,
      color: RC.inkPrimary,
      fontFamily: RC.sans
    }
  }, "Rent", /*#__PURE__*/React.createElement("span", {
    style: {
      color: RC.em100
    }
  }, "Check")), /*#__PURE__*/React.createElement("nav", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 24
    }
  }, links.map(l => /*#__PURE__*/React.createElement("button", {
    key: l.label,
    onClick: () => l.screen && onNavigate(l.screen),
    style: {
      background: 'none',
      border: 'none',
      cursor: 'pointer',
      fontSize: 13,
      fontFamily: RC.sans,
      fontWeight: 400,
      color: currentScreen === l.screen ? RC.inkPrimary : RC.inkSecondary,
      transition: `color ${RC.fast}`
    }
  }, l.label))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 8
    }
  }, /*#__PURE__*/React.createElement(RCButton, {
    variant: "ghost",
    size: "sm",
    onClick: () => onNavigate('login')
  }, "Prijavi se"), /*#__PURE__*/React.createElement(RCButton, {
    size: "sm",
    onClick: () => onNavigate('register')
  }, "Registruj se"))));
}

// ── Divider ──────────────────────────────────────────────────────────────
function Divider({
  style
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      height: '0.5px',
      background: 'rgba(46,66,104,0.8)',
      ...style
    }
  });
}
Object.assign(window, {
  RCButton,
  RCBadge,
  TrustRing,
  ScoreBar,
  RCCard,
  RCNavbar,
  Divider
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/rentcheck/Components.jsx", error: String((e && e.message) || e) }); }

// ui_kits/rentcheck/LandingScreen.jsx
try { (() => {
// RentCheck — Landing Page Screen

const {
  useState
} = React;
const STATS = [{
  value: '1.200+',
  label: 'Verifikovanih stanova'
}, {
  value: '4.800+',
  label: 'Recenzija'
}, {
  value: '340+',
  label: 'Agencija partnera'
}, {
  value: '98%',
  label: 'Zadovoljnih korisnika'
}];
const STEPS = [{
  num: '01',
  title: 'Stanodavac šalje invite kod',
  desc: 'Jedinstven kod ide zakupcu — dokaz da je interakcija zaista postojala. Bez koda nema recenzije.'
}, {
  num: '02',
  title: '48h čekaonica',
  desc: 'Stanodavac ima 48 sati da pripremi odgovor koji se objavljuje istovremeno sa recenzijom.'
}, {
  num: '03',
  title: 'Trust Score se ažurira',
  desc: 'Bayesian algoritam računa skor uzimajući u obzir starost, tip verifikacije i kategorije svake ocene.'
}];
const TIERS = [{
  score: 94,
  name: 'Elite Partner',
  range: '90–100'
}, {
  score: 78,
  name: 'Pouzdan',
  range: '70–89'
}, {
  score: 62,
  name: 'Neutralan',
  range: '50–69'
}, {
  score: 35,
  name: 'Pod proverom',
  range: '0–49'
}];
function LandingScreen({
  onNavigate
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      minHeight: '100vh',
      overflowX: 'hidden'
    }
  }, /*#__PURE__*/React.createElement("section", {
    style: {
      position: 'relative',
      padding: '72px 24px 80px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 0,
      left: '50%',
      transform: 'translateX(-50%)',
      width: 800,
      height: 500,
      borderRadius: '50%',
      background: 'radial-gradient(ellipse at center, rgba(29,158,117,0.08) 0%, transparent 70%)',
      pointerEvents: 'none',
      zIndex: 0
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: 1280,
      margin: '0 auto',
      position: 'relative',
      display: 'grid',
      gridTemplateColumns: '1fr 1fr',
      gap: 64,
      alignItems: 'center'
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "rc-fade-in"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'inline-flex',
      alignItems: 'center',
      gap: 6,
      borderRadius: 9999,
      border: '1px solid rgba(15,110,86,0.4)',
      background: 'rgba(15,110,86,0.1)',
      padding: '4px 12px',
      marginBottom: 20
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      width: 6,
      height: 6,
      borderRadius: '50%',
      background: RC.em400,
      display: 'block',
      animation: 'pulse 2s ease-in-out infinite'
    }
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 11,
      letterSpacing: '0.05em',
      color: RC.em100
    }
  }, "Reputaciona infrastruktura za rentiranje")), /*#__PURE__*/React.createElement("h1", {
    style: {
      fontSize: 44,
      fontWeight: 500,
      letterSpacing: '-0.04em',
      lineHeight: 1.1,
      color: RC.inkPrimary,
      marginBottom: 16
    }
  }, "Iznajmite stan", ' ', /*#__PURE__*/React.createElement("span", {
    style: {
      color: RC.em100
    }
  }, "sa poverenjem.")), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 14,
      color: RC.inkSecondary,
      lineHeight: 1.75,
      marginBottom: 28,
      maxWidth: 420
    }
  }, "Proverite reputaciju stanodavca pre potpisivanja ugovora. Verifikovane recenzije, Trust Score 0\u2013100, i Rental Passport koji prati tvoju istoriju kao zakupca."), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 10,
      flexWrap: 'wrap'
    }
  }, /*#__PURE__*/React.createElement(RCButton, {
    size: "lg",
    onClick: () => onNavigate('marketplace')
  }, "Pretra\u017Ei stanove", /*#__PURE__*/React.createElement("svg", {
    width: "14",
    height: "14",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M5 12h14M12 5l7 7-7 7"
  }))), /*#__PURE__*/React.createElement(RCButton, {
    variant: "outline",
    size: "lg",
    onClick: () => onNavigate('register')
  }, "Registruj se besplatno")), /*#__PURE__*/React.createElement("p", {
    style: {
      marginTop: 14,
      fontSize: 11,
      color: RC.inkTertiary
    }
  }, "Besplatno za zakupce \xB7 Bez kreditne kartice")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 10
    }
  }, /*#__PURE__*/React.createElement(RCCard, {
    style: {
      padding: 20
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      gap: 12
    }
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 11,
      color: RC.em100,
      letterSpacing: '0.03em'
    }
  }, "Stari Grad, Beograd"), /*#__PURE__*/React.createElement("h3", {
    style: {
      fontSize: 15,
      fontWeight: 500,
      color: RC.inkPrimary,
      marginTop: 2
    }
  }, "Dvosoban stan, 52m\xB2"), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 12,
      color: RC.inkSecondary,
      marginTop: 2
    }
  }, "600 EUR / mesec")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      gap: 4
    }
  }, /*#__PURE__*/React.createElement(TrustRing, {
    score: 94,
    size: "md"
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 10,
      color: RC.em100
    }
  }, "Elite Partner"))), /*#__PURE__*/React.createElement(Divider, {
    style: {
      margin: '14px 0'
    }
  }), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      color: RC.inkTertiary,
      marginBottom: 8
    }
  }, "Prosek kategorija"), [{
    label: 'Komunikacija',
    val: 98
  }, {
    label: 'Tačnost oglasa',
    val: 90
  }, {
    label: 'Povrat depozita',
    val: 88
  }].map(({
    label,
    val
  }) => /*#__PURE__*/React.createElement("div", {
    key: label,
    style: {
      marginBottom: 6
    }
  }, /*#__PURE__*/React.createElement(ScoreBar, {
    label: label,
    value: val
  })))), /*#__PURE__*/React.createElement(RCCard, {
    style: {
      padding: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between'
    }
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      color: RC.inkTertiary
    }
  }, "Rental Passport"), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 14,
      fontWeight: 500,
      color: RC.inkPrimary,
      marginTop: 2
    }
  }, "Milan Petrovi\u0107"), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 12,
      color: RC.inkSecondary
    }
  }, "3 iznajmljivanja \xB7 4.5 god. prose\u010Dno")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 8
    }
  }, /*#__PURE__*/React.createElement(TrustRing, {
    score: 87,
    size: "sm"
  }), /*#__PURE__*/React.createElement(RCBadge, {
    value: "gold",
    size: "sm"
  }))))))), /*#__PURE__*/React.createElement("section", {
    style: {
      borderTop: RC.border,
      borderBottom: RC.border,
      background: 'rgba(17,30,51,0.5)',
      padding: '32px 24px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: 1280,
      margin: '0 auto',
      display: 'grid',
      gridTemplateColumns: 'repeat(4, 1fr)',
      gap: 24
    }
  }, STATS.map(({
    value,
    label
  }) => /*#__PURE__*/React.createElement("div", {
    key: label,
    style: {
      textAlign: 'center'
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 26,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, value), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      color: RC.inkSecondary,
      marginTop: 2
    }
  }, label))))), /*#__PURE__*/React.createElement("section", {
    style: {
      padding: '72px 24px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: 1280,
      margin: '0 auto'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center',
      marginBottom: 48
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      fontWeight: 500,
      letterSpacing: '0.08em',
      textTransform: 'uppercase',
      color: RC.em400,
      marginBottom: 8
    }
  }, "Kako funkcioni\u0161e"), /*#__PURE__*/React.createElement("h2", {
    style: {
      fontSize: 28,
      fontWeight: 500,
      letterSpacing: '-0.03em',
      color: RC.inkPrimary
    }
  }, "Tri koraka do pouzdane informacije")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(3, 1fr)',
      gap: 16
    }
  }, STEPS.map(({
    num,
    title,
    desc
  }) => /*#__PURE__*/React.createElement(RCCard, {
    key: num,
    style: {
      padding: 24
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      marginBottom: 16
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: RC.mono,
      fontSize: 11,
      color: RC.inkTertiary
    }
  }, num), /*#__PURE__*/React.createElement("div", {
    style: {
      width: 36,
      height: 36,
      borderRadius: 8,
      border: '1px solid rgba(15,110,86,0.3)',
      background: 'rgba(15,110,86,0.1)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "16",
    height: "16",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: RC.em100,
    strokeWidth: "2"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"
  })))), /*#__PURE__*/React.createElement("h3", {
    style: {
      fontSize: 14,
      fontWeight: 500,
      color: RC.inkPrimary,
      marginBottom: 8
    }
  }, title), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 13,
      color: RC.inkSecondary,
      lineHeight: 1.65
    }
  }, desc)))))), /*#__PURE__*/React.createElement("section", {
    style: {
      borderTop: RC.border,
      borderBottom: RC.border,
      background: 'rgba(17,30,51,0.3)',
      padding: '72px 24px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: 1280,
      margin: '0 auto'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center',
      marginBottom: 48
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      fontWeight: 500,
      letterSpacing: '0.08em',
      textTransform: 'uppercase',
      color: RC.em400,
      marginBottom: 8
    }
  }, "Trust Score"), /*#__PURE__*/React.createElement("h2", {
    style: {
      fontSize: 28,
      fontWeight: 500,
      letterSpacing: '-0.03em',
      color: RC.inkPrimary
    }
  }, "Transparentna skala poverenja")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(4, 1fr)',
      gap: 12
    }
  }, TIERS.map(({
    score,
    name,
    range
  }) => {
    const t = RC.getTrust(score);
    return /*#__PURE__*/React.createElement("div", {
      key: name,
      style: {
        borderRadius: 12,
        border: `0.5px solid ${t.border}`,
        background: t.bg,
        padding: 24,
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        gap: 10
      }
    }, /*#__PURE__*/React.createElement(TrustRing, {
      score: score,
      size: "md"
    }), /*#__PURE__*/React.createElement("p", {
      style: {
        fontSize: 13,
        fontWeight: 500,
        color: RC.inkPrimary
      }
    }, name), /*#__PURE__*/React.createElement("p", {
      style: {
        fontFamily: RC.mono,
        fontSize: 11,
        color: RC.inkTertiary
      }
    }, range));
  })))), /*#__PURE__*/React.createElement("section", {
    style: {
      padding: '72px 24px',
      borderTop: RC.border
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: 560,
      margin: '0 auto',
      textAlign: 'center'
    }
  }, /*#__PURE__*/React.createElement("h2", {
    style: {
      fontSize: 28,
      fontWeight: 500,
      letterSpacing: '-0.03em',
      color: RC.inkPrimary
    }
  }, "Po\u010Dni da gradi\u0161 reputaciju danas."), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 13,
      color: RC.inkSecondary,
      marginTop: 12
    }
  }, "Besplatno za zakupce. B2B model za agencije."), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 10,
      justifyContent: 'center',
      marginTop: 28
    }
  }, /*#__PURE__*/React.createElement(RCButton, {
    size: "lg",
    onClick: () => onNavigate('register')
  }, "Registruj se besplatno"), /*#__PURE__*/React.createElement(RCButton, {
    variant: "secondary",
    size: "lg",
    onClick: () => onNavigate('marketplace')
  }, "Pretra\u017Ei stanove")))), /*#__PURE__*/React.createElement("footer", {
    style: {
      borderTop: RC.border,
      padding: '24px',
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      maxWidth: 1280,
      margin: '0 auto'
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 14,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, "Rent", /*#__PURE__*/React.createElement("span", {
    style: {
      color: RC.em100
    }
  }, "Check")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 24
    }
  }, ['O platformi', 'Pravni kutak'].map(l => /*#__PURE__*/React.createElement("span", {
    key: l,
    style: {
      fontSize: 11,
      color: RC.inkTertiary,
      cursor: 'pointer'
    }
  }, l))), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      color: RC.inkTertiary
    }
  }, "\xA9 2025 RentCheck")), /*#__PURE__*/React.createElement("style", null, `@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }`));
}
Object.assign(window, {
  LandingScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/rentcheck/LandingScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/rentcheck/MarketplaceScreen.jsx
try { (() => {
// RentCheck — Marketplace Screen

const {
  useState
} = React;
const MUNICIPALITIES = ['Sve opštine', 'Stari Grad', 'Vračar', 'Palilula', 'Savski venac', 'Zemun', 'Novi Beograd'];
const PROPERTIES = [{
  id: '1',
  title: 'Dvosoban stan, 52m²',
  address: 'Knez Mihailova 14',
  municipality: 'Stari Grad',
  price: 600,
  trustScore: 94,
  reviewCount: 12,
  agency: 'Remax',
  hasIR: true
}, {
  id: '2',
  title: 'Jednosoban, 35m²',
  address: 'Svetozara Markovića 8',
  municipality: 'Vračar',
  price: 420,
  trustScore: 81,
  reviewCount: 7,
  agency: null,
  hasIR: false
}, {
  id: '3',
  title: 'Trosoban stan, 78m²',
  address: 'Bulevar oslobođenja 22',
  municipality: 'Savski venac',
  price: 850,
  trustScore: 73,
  reviewCount: 5,
  agency: 'ERA',
  hasIR: true
}, {
  id: '4',
  title: 'Garsonjera, 22m²',
  address: 'Skadarska 3',
  municipality: 'Stari Grad',
  price: 280,
  trustScore: 62,
  reviewCount: 3,
  agency: null,
  hasIR: false
}, {
  id: '5',
  title: 'Dvosoban, 48m²',
  address: 'Vojvode Stepe 44',
  municipality: 'Palilula',
  price: 480,
  trustScore: 55,
  reviewCount: 4,
  agency: null,
  hasIR: false
}, {
  id: '6',
  title: 'Četvorosoban, 110m²',
  address: 'Knjaževačka 2',
  municipality: 'Zemun',
  price: 1100,
  trustScore: 89,
  reviewCount: 9,
  agency: 'Remax',
  hasIR: true
}];
function PropertyCardUI({
  property,
  onClick
}) {
  const [hov, setHov] = useState(false);
  const t = RC.getTrust(property.trustScore);
  return /*#__PURE__*/React.createElement("div", {
    onClick: onClick,
    onMouseEnter: () => setHov(true),
    onMouseLeave: () => setHov(false),
    style: {
      borderRadius: 12,
      border: `0.5px solid ${hov ? 'rgba(15,110,86,0.4)' : 'rgba(46,66,104,0.8)'}`,
      background: RC.raised,
      overflow: 'hidden',
      cursor: 'pointer',
      transition: `border-color ${RC.fast}`,
      display: 'flex',
      flexDirection: 'column',
      boxShadow: '0 1px 3px rgba(0,0,0,0.4)'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      height: 140,
      background: RC.elevated,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      position: 'relative'
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "28",
    height: "28",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "rgba(46,66,104,0.8)",
    strokeWidth: "1.5"
  }, /*#__PURE__*/React.createElement("rect", {
    x: "2",
    y: "7",
    width: "20",
    height: "15",
    rx: "2"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M16 21V8a2 2 0 0 0-2-2H10a2 2 0 0 0-2 2v13"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M2 12l10-9 10 9"
  })), property.hasIR && /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 8,
      left: 8,
      display: 'flex',
      alignItems: 'center',
      gap: 4,
      borderRadius: 9999,
      border: '1px solid rgba(15,110,86,0.4)',
      background: 'rgba(10,22,40,0.8)',
      backdropFilter: 'blur(6px)',
      padding: '3px 8px'
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "10",
    height: "10",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: RC.em100,
    strokeWidth: "2"
  }, /*#__PURE__*/React.createElement("polyline", {
    points: "23 6 13.5 15.5 8.5 10.5 1 18"
  }), /*#__PURE__*/React.createElement("polyline", {
    points: "17 6 23 6 23 12"
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 10,
      fontWeight: 500,
      color: RC.em100
    }
  }, "Investment Radar")), /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 8,
      right: 8,
      background: 'rgba(10,22,40,0.7)',
      borderRadius: 9999,
      padding: 2,
      backdropFilter: 'blur(4px)'
    }
  }, /*#__PURE__*/React.createElement(TrustRing, {
    score: property.trustScore,
    size: "sm"
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: 14,
      display: 'flex',
      flexDirection: 'column',
      flex: 1
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 4,
      marginBottom: 4
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "10",
    height: "10",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: RC.em100,
    strokeWidth: "2.5"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "12",
    cy: "10",
    r: "3"
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 11,
      color: RC.em100
    }
  }, property.municipality)), /*#__PURE__*/React.createElement("h3", {
    style: {
      fontSize: 14,
      fontWeight: 500,
      color: RC.inkPrimary,
      lineHeight: 1.3
    }
  }, property.title), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      color: RC.inkSecondary,
      marginTop: 2
    }
  }, property.address), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 17,
      fontWeight: 500,
      color: RC.inkPrimary,
      marginTop: 10
    }
  }, property.price.toLocaleString('sr-RS'), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 11,
      fontWeight: 400,
      color: RC.inkTertiary,
      marginLeft: 4
    }
  }, "EUR / mes.")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      borderTop: RC.border,
      paddingTop: 10,
      marginTop: 'auto',
      marginBottom: 0,
      paddingBottom: 0
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 6
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 11,
      color: RC.inkSecondary
    }
  }, "Stanodavac"), property.agency && /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 10,
      fontWeight: 500,
      background: '#085041',
      color: RC.em100,
      borderRadius: 9999,
      padding: '1px 6px'
    }
  }, "Agencija")), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 11,
      color: RC.inkTertiary
    }
  }, property.reviewCount, " recenzija"))));
}
function MarketplaceScreen({
  onNavigate,
  onSelectProperty
}) {
  const [municipality, setMunicipality] = useState('Sve opštine');
  const [minScore, setMinScore] = useState(0);
  const filtered = PROPERTIES.filter(p => (municipality === 'Sve opštine' || p.municipality === municipality) && p.trustScore >= minScore);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      minHeight: '100vh'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: 1280,
      margin: '0 auto',
      padding: '32px 24px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: 24
    }
  }, /*#__PURE__*/React.createElement("h1", {
    style: {
      fontSize: 26,
      fontWeight: 500,
      letterSpacing: '-0.03em',
      color: RC.inkPrimary
    }
  }, "Pretra\u017Ei stanove"), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 13,
      color: RC.inkSecondary,
      marginTop: 4
    }
  }, "Verifikovane nekretnine sa Trust Score ocenom stanodavca.")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: 10,
      marginBottom: 24,
      flexWrap: 'wrap',
      padding: '14px 16px',
      background: RC.raised,
      borderRadius: 10,
      border: RC.border
    }
  }, /*#__PURE__*/React.createElement("select", {
    value: municipality,
    onChange: e => setMunicipality(e.target.value),
    style: {
      fontFamily: RC.sans,
      fontSize: 13,
      color: RC.inkPrimary,
      background: RC.elevated,
      border: RC.border,
      borderRadius: 8,
      padding: '7px 12px',
      outline: 'none'
    }
  }, MUNICIPALITIES.map(m => /*#__PURE__*/React.createElement("option", {
    key: m
  }, m))), /*#__PURE__*/React.createElement("select", {
    value: minScore,
    onChange: e => setMinScore(Number(e.target.value)),
    style: {
      fontFamily: RC.sans,
      fontSize: 13,
      color: RC.inkPrimary,
      background: RC.elevated,
      border: RC.border,
      borderRadius: 8,
      padding: '7px 12px',
      outline: 'none'
    }
  }, /*#__PURE__*/React.createElement("option", {
    value: 0
  }, "Svi Trust Score"), /*#__PURE__*/React.createElement("option", {
    value: 70
  }, "70+ Pouzdan"), /*#__PURE__*/React.createElement("option", {
    value: 90
  }, "90+ Elite Partner")), /*#__PURE__*/React.createElement("div", {
    style: {
      marginLeft: 'auto',
      display: 'flex',
      alignItems: 'center'
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 12,
      color: RC.inkTertiary
    }
  }, filtered.length, " rezultata"))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(3, 1fr)',
      gap: 16
    }
  }, filtered.map(p => /*#__PURE__*/React.createElement(PropertyCardUI, {
    key: p.id,
    property: p,
    onClick: () => onSelectProperty(p)
  }))), filtered.length === 0 && /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center',
      padding: '80px 24px',
      color: RC.inkSecondary
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "40",
    height: "40",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "rgba(46,66,104,0.8)",
    strokeWidth: "1.5",
    style: {
      display: 'block',
      margin: '0 auto 16px'
    }
  }, /*#__PURE__*/React.createElement("rect", {
    x: "2",
    y: "7",
    width: "20",
    height: "15",
    rx: "2"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M2 12l10-9 10 9"
  })), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 15,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, "Nema rezultata"), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 13,
      marginTop: 6
    }
  }, "Poku\u0161aj sa druga\u010Dijim filterima."))));
}
Object.assign(window, {
  MarketplaceScreen,
  PROPERTIES
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/rentcheck/MarketplaceScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/rentcheck/PassportScreen.jsx
try { (() => {
// RentCheck — Passport & Auth Screens

const {
  useState
} = React;

// ── Rental Passport ──────────────────────────────────────────────────────
const PASSPORT_DATA = {
  tenant: {
    name: 'Milan Petrović',
    verificationLevel: 'gold'
  },
  passport: {
    score: 87,
    avgStayMonths: 18,
    reviewCount: 3,
    badges: ['Uredan', 'Plaća na vreme', 'Dobar komšija']
  }
};
const TENANT_CATEGORIES = [{
  label: 'Plaćanje stanarine',
  value: 95
}, {
  label: 'Održavanje stana',
  value: 88
}, {
  label: 'Odnos sa komšijama',
  value: 82
}];
function PassportScreen({
  onNavigate
}) {
  const {
    tenant,
    passport
  } = PASSPORT_DATA;
  const t = RC.getTrust(passport.score);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      minHeight: '100vh'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: 780,
      margin: '0 auto',
      padding: '40px 24px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: 28
    }
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      fontWeight: 500,
      textTransform: 'uppercase',
      letterSpacing: '0.08em',
      color: RC.em400,
      marginBottom: 4
    }
  }, "Rental Passport"), /*#__PURE__*/React.createElement("h1", {
    style: {
      fontSize: 28,
      fontWeight: 500,
      letterSpacing: '-0.03em',
      color: RC.inkPrimary
    }
  }, "Moj profil zakupca")), /*#__PURE__*/React.createElement(RCButton, {
    variant: "outline",
    size: "sm"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "13",
    height: "13",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"
  }), /*#__PURE__*/React.createElement("polyline", {
    points: "16 6 12 2 8 6"
  }), /*#__PURE__*/React.createElement("line", {
    x1: "12",
    y1: "2",
    x2: "12",
    y2: "15"
  })), "Podeli Passport")), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '300px 1fr',
      gap: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 12
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      borderRadius: 12,
      border: `0.5px solid ${t.border}`,
      background: t.bg,
      padding: 24
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'flex-start',
      marginBottom: 20
    }
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      color: RC.inkTertiary,
      marginBottom: 4
    }
  }, "Rental Passport"), /*#__PURE__*/React.createElement("h2", {
    style: {
      fontSize: 18,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, tenant.name), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 6
    }
  }, /*#__PURE__*/React.createElement(RCBadge, {
    value: tenant.verificationLevel,
    size: "sm"
  })))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 16,
      marginBottom: 20
    }
  }, /*#__PURE__*/React.createElement(TrustRing, {
    score: passport.score,
    size: "lg"
  }), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 28,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, passport.score, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 13,
      fontWeight: 400,
      color: RC.inkTertiary
    }
  }, "/100")), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 13,
      color: t.stroke
    }
  }, t.label))), /*#__PURE__*/React.createElement(Divider, {
    style: {
      marginBottom: 16
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(3, 1fr)',
      textAlign: 'center',
      gap: 4
    }
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 18,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, passport.avgStayMonths), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 10,
      color: RC.inkTertiary
    }
  }, "mes. prosek")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 18,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, passport.reviewCount), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 10,
      color: RC.inkTertiary
    }
  }, "recenzija")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 18,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, passport.badges.length), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 10,
      color: RC.inkTertiary
    }
  }, "bed\u017Eeva")))), /*#__PURE__*/React.createElement(RCCard, {
    style: {
      padding: 16
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      fontWeight: 500,
      textTransform: 'uppercase',
      letterSpacing: '0.06em',
      color: RC.inkTertiary,
      marginBottom: 10
    }
  }, "Bed\u017Eevi"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexWrap: 'wrap',
      gap: 6
    }
  }, passport.badges.map(b => /*#__PURE__*/React.createElement("span", {
    key: b,
    style: {
      fontSize: 11,
      fontWeight: 500,
      background: 'rgba(29,158,117,0.1)',
      color: RC.em100,
      borderRadius: 9999,
      padding: '3px 10px',
      border: '0.5px solid rgba(29,158,117,0.3)'
    }
  }, b))))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 16
    }
  }, /*#__PURE__*/React.createElement(RCCard, {
    style: {
      padding: 20
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      fontWeight: 500,
      textTransform: 'uppercase',
      letterSpacing: '0.06em',
      color: RC.inkTertiary,
      marginBottom: 14
    }
  }, "Ocene kao zakupac"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 10
    }
  }, TENANT_CATEGORIES.map(c => /*#__PURE__*/React.createElement(ScoreBar, {
    key: c.label,
    label: c.label,
    value: c.value
  })))), /*#__PURE__*/React.createElement(RCCard, {
    style: {
      padding: 20
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      fontWeight: 500,
      textTransform: 'uppercase',
      letterSpacing: '0.06em',
      color: RC.inkTertiary,
      marginBottom: 14
    }
  }, "Istorija iznajmljivanja"), [{
    addr: 'Knez Mihailova 14, Stari Grad',
    period: 'Mar 2023 – Feb 2025',
    score: 92
  }, {
    addr: 'Vojvode Stepe 44, Palilula',
    period: 'Jan 2021 – Feb 2023',
    score: 84
  }, {
    addr: 'Bulevar oslobođenja 22',
    period: 'Jun 2019 – Dec 2020',
    score: 78
  }].map((r, i) => /*#__PURE__*/React.createElement("div", {
    key: i,
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      padding: '10px 0',
      borderBottom: i < 2 ? RC.border : 'none'
    }
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 13,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, r.addr), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      color: RC.inkTertiary,
      marginTop: 2
    }
  }, r.period)), /*#__PURE__*/React.createElement(TrustRing, {
    score: r.score,
    size: "sm"
  })))), /*#__PURE__*/React.createElement(RCButton, {
    onClick: () => onNavigate('marketplace'),
    style: {
      alignSelf: 'flex-start'
    }
  }, "Pretra\u017Ei stanove", /*#__PURE__*/React.createElement("svg", {
    width: "13",
    height: "13",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M5 12h14M12 5l7 7-7 7"
  })))))));
}

// ── Auth Screen (Login + Register) ────────────────────────────────────────
function AuthScreen({
  mode = 'login',
  onNavigate
}) {
  const [currentMode, setCurrentMode] = useState(mode);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [name, setName] = useState('');
  const [remember, setRemember] = useState(false);
  const inputStyle = {
    fontFamily: RC.sans,
    fontSize: 14,
    color: RC.inkPrimary,
    background: RC.elevated,
    border: RC.border,
    borderRadius: 8,
    padding: '10px 14px',
    outline: 'none',
    width: '100%',
    transition: `border-color ${RC.fast}, box-shadow ${RC.fast}`
  };
  return /*#__PURE__*/React.createElement("div", {
    style: {
      minHeight: '100vh',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      padding: 24
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      maxWidth: 400
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center',
      marginBottom: 28
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 22,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, "Rent", /*#__PURE__*/React.createElement("span", {
    style: {
      color: RC.em100
    }
  }, "Check")), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 14,
      color: RC.inkSecondary,
      marginTop: 8
    }
  }, currentMode === 'login' ? 'Prijavi se na svoj nalog' : 'Kreiraj besplatan nalog')), /*#__PURE__*/React.createElement(RCCard, {
    style: {
      padding: 28
    }
  }, currentMode === 'register' && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(3,1fr)',
      gap: 6,
      marginBottom: 20
    }
  }, ['Zakupac', 'Stanodavac', 'Agencija'].map((role, i) => /*#__PURE__*/React.createElement("button", {
    key: role,
    style: {
      background: i === 0 ? 'rgba(29,158,117,0.15)' : RC.elevated,
      border: i === 0 ? '1px solid rgba(29,158,117,0.4)' : RC.border,
      borderRadius: 8,
      padding: '8px 4px',
      fontSize: 12,
      fontWeight: 500,
      color: i === 0 ? RC.em100 : RC.inkSecondary,
      cursor: 'pointer',
      fontFamily: RC.sans
    }
  }, role))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 14
    }
  }, currentMode === 'register' && /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("label", {
    style: {
      fontSize: 12,
      color: RC.inkSecondary,
      display: 'block',
      marginBottom: 5
    }
  }, "Ime i prezime"), /*#__PURE__*/React.createElement("input", {
    style: inputStyle,
    placeholder: "Milan Petrovi\u0107",
    value: name,
    onChange: e => setName(e.target.value)
  })), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("label", {
    style: {
      fontSize: 12,
      color: RC.inkSecondary,
      display: 'block',
      marginBottom: 5
    }
  }, "E-mail adresa"), /*#__PURE__*/React.createElement("input", {
    style: inputStyle,
    type: "email",
    placeholder: "milan@primer.rs",
    value: email,
    onChange: e => setEmail(e.target.value)
  })), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: 5
    }
  }, /*#__PURE__*/React.createElement("label", {
    style: {
      fontSize: 12,
      color: RC.inkSecondary
    }
  }, "Lozinka"), currentMode === 'login' && /*#__PURE__*/React.createElement("button", {
    style: {
      fontSize: 11,
      color: RC.em100,
      background: 'none',
      border: 'none',
      cursor: 'pointer',
      fontFamily: RC.sans
    }
  }, "Zaboravili ste?")), /*#__PURE__*/React.createElement("input", {
    style: inputStyle,
    type: "password",
    placeholder: "\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022",
    value: password,
    onChange: e => setPassword(e.target.value)
  })), currentMode === 'login' && /*#__PURE__*/React.createElement("label", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 8,
      fontSize: 12,
      color: RC.inkSecondary,
      cursor: 'pointer'
    }
  }, /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: remember,
    onChange: e => setRemember(e.target.checked),
    style: {
      accentColor: RC.em400
    }
  }), "Zapamti me (7 dana)"), /*#__PURE__*/React.createElement(RCButton, {
    style: {
      width: '100%',
      marginTop: 4
    },
    onClick: () => onNavigate('passport')
  }, currentMode === 'login' ? 'Prijavi se' : 'Registruj se besplatno')), /*#__PURE__*/React.createElement(Divider, {
    style: {
      margin: '20px 0'
    }
  }), /*#__PURE__*/React.createElement("p", {
    style: {
      textAlign: 'center',
      fontSize: 12,
      color: RC.inkTertiary
    }
  }, currentMode === 'login' ? 'Nemaš nalog? ' : 'Već imaš nalog? ', /*#__PURE__*/React.createElement("button", {
    onClick: () => setCurrentMode(currentMode === 'login' ? 'register' : 'login'),
    style: {
      color: RC.em100,
      background: 'none',
      border: 'none',
      cursor: 'pointer',
      fontSize: 12,
      fontFamily: RC.sans
    }
  }, currentMode === 'login' ? 'Registruj se' : 'Prijavi se'))), /*#__PURE__*/React.createElement("p", {
    style: {
      textAlign: 'center',
      fontSize: 11,
      color: RC.inkTertiary,
      marginTop: 16
    }
  }, "Registracijom prihvata\u0161 Uslove kori\u0161\u0107enja i Politiku privatnosti.")));
}
Object.assign(window, {
  PassportScreen,
  AuthScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/rentcheck/PassportScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/rentcheck/PropertyScreen.jsx
try { (() => {
// RentCheck — Property Detail Screen

const {
  useState
} = React;
const CATEGORIES_PROPERTY = [{
  label: 'Komunikacija',
  key: 'communication'
}, {
  label: 'Tačnost oglasa',
  key: 'accuracy'
}, {
  label: 'Povrat depozita',
  key: 'deposit'
}, {
  label: 'Popravke',
  key: 'repairs'
}, {
  label: 'Privatnost',
  key: 'privacy'
}, {
  label: 'Buka',
  key: 'noise'
}, {
  label: 'Troškovi',
  key: 'costs'
}, {
  label: 'Uređaji',
  key: 'appliances'
}];
const SAMPLE_REVIEWS = [{
  id: '1',
  author: 'Ana P.',
  score: 94,
  status: 'published',
  verification: 'id_verified',
  body: 'Stan je odgovarao opisu, stanodavac je uvek bio dostupan i odgovorio na svaki problem u roku 24 sata.',
  date: 'Feb 2025',
  scores: {
    communication: 98,
    accuracy: 90,
    deposit: 95,
    repairs: 88,
    privacy: 92,
    noise: 75,
    costs: 82,
    appliances: 85
  }
}, {
  id: '2',
  author: 'Marko S.',
  score: 78,
  status: 'published',
  verification: 'gold',
  body: 'Generalno zadovoljan, mada su troškovi grejanja bili nešto viši nego što je najavljeno u oglasu.',
  date: 'Nov 2024',
  scores: {
    communication: 85,
    accuracy: 72,
    deposit: 88,
    repairs: 75,
    privacy: 80,
    noise: 65,
    costs: 58,
    appliances: 78
  }
}];
function ReviewItem({
  review
}) {
  const [expanded, setExpanded] = useState(false);
  return /*#__PURE__*/React.createElement("div", {
    style: {
      borderRadius: 10,
      border: RC.border,
      background: RC.raised,
      padding: 16
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'flex-start',
      marginBottom: 10
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 10
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width: 32,
      height: 32,
      borderRadius: 9999,
      background: RC.elevated,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      fontSize: 12,
      fontWeight: 500,
      color: RC.inkSecondary
    }
  }, review.author[0]), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 6
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 13,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, review.author), /*#__PURE__*/React.createElement(RCBadge, {
    value: review.verification,
    size: "sm"
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 11,
      color: RC.inkTertiary
    }
  }, review.date))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 8
    }
  }, /*#__PURE__*/React.createElement(RCBadge, {
    value: review.status,
    size: "sm"
  }), /*#__PURE__*/React.createElement(TrustRing, {
    score: review.score,
    size: "sm"
  }))), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 13,
      color: RC.inkSecondary,
      lineHeight: 1.65
    }
  }, review.body), /*#__PURE__*/React.createElement("button", {
    onClick: () => setExpanded(!expanded),
    style: {
      background: 'none',
      border: 'none',
      color: RC.em100,
      fontSize: 11,
      cursor: 'pointer',
      marginTop: 8,
      fontFamily: RC.sans,
      padding: 0
    }
  }, expanded ? 'Sakrij ocene ▲' : 'Prikaži ocene ▼'), expanded && /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: 10,
      display: 'flex',
      flexDirection: 'column',
      gap: 6
    }
  }, CATEGORIES_PROPERTY.map(c => /*#__PURE__*/React.createElement(ScoreBar, {
    key: c.key,
    label: c.label,
    value: review.scores[c.key]
  }))));
}
function PropertyScreen({
  property,
  onBack,
  onNavigate
}) {
  const t = RC.getTrust(property.trustScore);
  const avgScores = {
    communication: 92,
    accuracy: 81,
    deposit: 92,
    repairs: 82,
    privacy: 86,
    noise: 70,
    costs: 70,
    appliances: 82
  };
  return /*#__PURE__*/React.createElement("div", {
    style: {
      minHeight: '100vh'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth: 1280,
      margin: '0 auto',
      padding: '24px 24px'
    }
  }, /*#__PURE__*/React.createElement("button", {
    onClick: onBack,
    style: {
      background: 'none',
      border: 'none',
      color: RC.inkTertiary,
      fontSize: 12,
      cursor: 'pointer',
      fontFamily: RC.sans,
      display: 'flex',
      alignItems: 'center',
      gap: 4,
      marginBottom: 20
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "12",
    height: "12",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M19 12H5M12 5l-7 7 7 7"
  })), "Nazad na listu"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '1fr 340px',
      gap: 24,
      alignItems: 'start'
    }
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    style: {
      height: 280,
      background: RC.elevated,
      borderRadius: 12,
      border: RC.border,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      marginBottom: 20,
      position: 'relative'
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "40",
    height: "40",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "rgba(46,66,104,0.8)",
    strokeWidth: "1.5"
  }, /*#__PURE__*/React.createElement("rect", {
    x: "2",
    y: "7",
    width: "20",
    height: "15",
    rx: "2"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M2 12l10-9 10 9"
  })), property.hasIR && /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 12,
      left: 12,
      display: 'flex',
      alignItems: 'center',
      gap: 4,
      borderRadius: 9999,
      border: '1px solid rgba(15,110,86,0.4)',
      background: 'rgba(10,22,40,0.85)',
      padding: '4px 10px'
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "11",
    height: "11",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: RC.em100,
    strokeWidth: "2"
  }, /*#__PURE__*/React.createElement("polyline", {
    points: "23 6 13.5 15.5 8.5 10.5 1 18"
  }), /*#__PURE__*/React.createElement("polyline", {
    points: "17 6 23 6 23 12"
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 11,
      color: RC.em100
    }
  }, "Investment Radar \xB7 +12% / 3 god."))), /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: 20
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 5,
      marginBottom: 6
    }
  }, /*#__PURE__*/React.createElement("svg", {
    width: "11",
    height: "11",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: RC.em100,
    strokeWidth: "2.5"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"
  }), /*#__PURE__*/React.createElement("circle", {
    cx: "12",
    cy: "10",
    r: "3"
  })), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 12,
      color: RC.em100
    }
  }, property.municipality, " \xB7 ", property.address)), /*#__PURE__*/React.createElement("h1", {
    style: {
      fontSize: 26,
      fontWeight: 500,
      letterSpacing: '-0.03em',
      color: RC.inkPrimary
    }
  }, property.title), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 20,
      fontWeight: 500,
      color: RC.inkPrimary,
      marginTop: 6
    }
  }, property.price.toLocaleString('sr-RS'), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 12,
      fontWeight: 400,
      color: RC.inkTertiary,
      marginLeft: 6
    }
  }, "EUR / mesec"))), /*#__PURE__*/React.createElement(RCCard, {
    style: {
      padding: 20,
      marginBottom: 20
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      fontWeight: 500,
      textTransform: 'uppercase',
      letterSpacing: '0.06em',
      color: RC.inkTertiary,
      marginBottom: 14
    }
  }, "Prosek po kategorijama"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 8
    }
  }, CATEGORIES_PROPERTY.map(c => /*#__PURE__*/React.createElement(ScoreBar, {
    key: c.key,
    label: c.label,
    value: avgScores[c.key]
  })))), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 14,
      fontWeight: 500,
      color: RC.inkPrimary,
      marginBottom: 12
    }
  }, SAMPLE_REVIEWS.length, " recenzija"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 10
    }
  }, SAMPLE_REVIEWS.map(r => /*#__PURE__*/React.createElement(ReviewItem, {
    key: r.id,
    review: r
  }))))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 12
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      borderRadius: 12,
      border: `0.5px solid ${t.border}`,
      background: t.bg,
      padding: 20,
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      gap: 10
    }
  }, /*#__PURE__*/React.createElement(TrustRing, {
    score: property.trustScore,
    size: "lg"
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center'
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 26,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, property.trustScore, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: 13,
      fontWeight: 400,
      color: RC.inkTertiary
    }
  }, "/100")), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 13,
      color: t.stroke
    }
  }, t.label))), /*#__PURE__*/React.createElement(RCCard, {
    style: {
      padding: 20
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 11,
      color: RC.inkTertiary,
      marginBottom: 12
    }
  }, "Stanodavac"), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      marginBottom: 14
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      width: 36,
      height: 36,
      borderRadius: 9999,
      background: RC.elevated,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      fontSize: 14,
      fontWeight: 500,
      color: RC.inkSecondary
    }
  }, "J"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: 13,
      fontWeight: 500,
      color: RC.inkPrimary
    }
  }, "Jovan Nikoli\u0107"), /*#__PURE__*/React.createElement(RCBadge, {
    value: "gold",
    size: "sm"
  }))), /*#__PURE__*/React.createElement(RCButton, {
    style: {
      width: '100%'
    },
    onClick: () => onNavigate('register')
  }, "Kontaktiraj stanodavca")), /*#__PURE__*/React.createElement(RCButton, {
    variant: "outline",
    style: {
      width: '100%'
    },
    onClick: () => onNavigate('register')
  }, "Napi\u0161i recenziju")))));
}
Object.assign(window, {
  PropertyScreen
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/rentcheck/PropertyScreen.jsx", error: String((e && e.message) || e) }); }

// ui_kits/rentcheck/Tokens.jsx
try { (() => {
// RentCheck — Shared Tokens & Utilities
// Exported to window for use across UI kit components

const RC = {
  // Surfaces
  base: '#0A1628',
  raised: '#111E33',
  elevated: '#1A2D47',
  overlay: '#243656',
  border: '0.5px solid rgba(46,66,104,0.8)',
  borderColor: '#2E4268',
  // Emerald
  em400: '#1D9E75',
  em600: '#0F6E56',
  em100: '#9FE1CB',
  em200: '#5DCAA5',
  // Ink
  inkPrimary: '#EDF2F7',
  inkSecondary: '#8DA4BE',
  inkTertiary: '#4A6280',
  inkAccent: '#9FE1CB',
  // Trust tiers
  trust: {
    low: {
      stroke: '#E24B4A',
      text: '#F09595',
      label: 'Pod proverom',
      bg: 'rgba(226,75,74,0.06)',
      border: 'rgba(226,75,74,0.3)'
    },
    mid: {
      stroke: '#EF9F27',
      text: '#FAC775',
      label: 'Neutralan',
      bg: 'rgba(239,159,39,0.06)',
      border: 'rgba(239,159,39,0.3)'
    },
    good: {
      stroke: '#639922',
      text: '#C0DD97',
      label: 'Pouzdan',
      bg: 'rgba(99,153,34,0.06)',
      border: 'rgba(99,153,34,0.3)'
    },
    premium: {
      stroke: '#1D9E75',
      text: '#9FE1CB',
      label: 'Elite Partner',
      bg: 'rgba(29,158,117,0.06)',
      border: 'rgba(29,158,117,0.3)'
    }
  },
  getTier(score) {
    if (score < 50) return 'low';
    if (score < 70) return 'mid';
    if (score < 90) return 'good';
    return 'premium';
  },
  getTrust(score) {
    return RC.trust[RC.getTier(score)];
  },
  // Badges
  verificationBadges: {
    id_verified: {
      bg: '#0C447C',
      text: '#B5D4F4',
      label: 'ID Verifikovan'
    },
    gold: {
      bg: '#0F6E56',
      text: '#9FE1CB',
      label: 'Gold verifikovan'
    },
    silver: {
      bg: '#3d3d3a',
      text: '#D3D1C7',
      label: 'Silver verifikovan'
    },
    unverified: {
      bg: '#1a1a18',
      text: '#888780',
      label: 'Neverifikovan'
    }
  },
  reviewStatus: {
    published: {
      bg: '#0F6E56',
      text: '#9FE1CB',
      label: 'Objavljeno'
    },
    pending: {
      bg: '#633806',
      text: '#FAC775',
      label: 'Na čekanju'
    },
    disputed: {
      bg: '#A32D2D',
      text: '#F7C1C1',
      label: 'Sporno'
    }
  },
  // Type
  sans: "'DM Sans', system-ui, sans-serif",
  mono: "'JetBrains Mono', monospace",
  // Transitions
  ease: 'cubic-bezier(0.16, 1, 0.3, 1)',
  fast: '120ms cubic-bezier(0.16, 1, 0.3, 1)',
  base: '200ms cubic-bezier(0.16, 1, 0.3, 1)'
};

// ── Shared component styles ───────────────────────────────────────────────

const rcStyles = `
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500&family=JetBrains+Mono:wght@400;500&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { font-size: 14px; -webkit-font-smoothing: antialiased; }
  body { font-family: 'DM Sans', sans-serif; background: #0A1628; color: #EDF2F7; min-height: 100vh; }

  ::-webkit-scrollbar { width: 5px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #2E4268; border-radius: 9999px; }

  ::selection { background: rgba(29,158,117,0.3); }

  :focus-visible { outline: 2px solid #1D9E75; outline-offset: 2px; border-radius: 4px; }

  .rc-fade-in { animation: rcFadeIn 0.2s cubic-bezier(0.16,1,0.3,1) both; }
  @keyframes rcFadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
`;
Object.assign(window, {
  RC,
  rcStyles
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/rentcheck/Tokens.jsx", error: String((e && e.message) || e) }); }

})();
