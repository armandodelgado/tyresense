// ══ Mock Data — Compatible with TyreSense structure ══
const FLEET = [
  {id:'4821',model:'Kenworth T680 · Eje triple',route:'CDMX → Monterrey',driver:'Carlos R.',lastInspection:'2026-05-17T09:30:00',km:234500,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:82,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0142',cost:8500},
    {pos:'D-D',label:'Delantera Der',pct:78,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0142',cost:8500},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:65,status:'ok',supplier:'Michelin',lot:'LOT-2026-0098',cost:9200},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:51,status:'warning',supplier:'Michelin',lot:'LOT-2026-0098',cost:9200},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:48,status:'warning',supplier:'Continental',lot:'LOT-2026-0115',cost:7800},
    {pos:'T1D',label:'Tractivo 1 Der',pct:70,status:'ok',supplier:'Continental',lot:'LOT-2026-0115',cost:7800},
    {pos:'T2I',label:'Trasero 2 Izq',pct:74,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0142',cost:8500},
    {pos:'T2Ii',label:'Trasero 2 IzqI',pct:28,status:'critical',supplier:'Hankook',lot:'LOT-2026-0087',cost:6200},
    {pos:'T2Di',label:'Trasero 2 DerI',pct:66,status:'ok',supplier:'Hankook',lot:'LOT-2026-0087',cost:6200},
    {pos:'T2D',label:'Trasero 2 Der',pct:72,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0142',cost:8500}
  ]},
  {id:'5033',model:'Freightliner Cascadia · Eje doble',route:'Guadalajara → León',driver:'Miguel A.',lastInspection:'2026-05-17T07:15:00',km:187200,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:91,status:'ok',supplier:'Michelin',lot:'LOT-2026-0101',cost:9200},
    {pos:'D-D',label:'Delantera Der',pct:88,status:'ok',supplier:'Michelin',lot:'LOT-2026-0101',cost:9200},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:79,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0145',cost:8500},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:75,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0145',cost:8500},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:80,status:'ok',supplier:'Continental',lot:'LOT-2026-0120',cost:7800},
    {pos:'T1D',label:'Tractivo 1 Der',pct:83,status:'ok',supplier:'Continental',lot:'LOT-2026-0120',cost:7800}
  ]},
  {id:'3917',model:'International LT · Eje triple',route:'Monterrey → Laredo',driver:'José L.',lastInspection:'2026-05-16T16:40:00',km:312000,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:70,status:'ok',supplier:'Continental',lot:'LOT-2026-0110',cost:7800},
    {pos:'D-D',label:'Delantera Der',pct:55,status:'ok',supplier:'Continental',lot:'LOT-2026-0110',cost:7800},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:42,status:'warning',supplier:'Hankook',lot:'LOT-2026-0089',cost:6200},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:38,status:'warning',supplier:'Hankook',lot:'LOT-2026-0089',cost:6200},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:60,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0150',cost:8500},
    {pos:'T1D',label:'Tractivo 1 Der',pct:62,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0150',cost:8500},
    {pos:'T2I',label:'Trasero 2 Izq',pct:55,status:'ok',supplier:'Michelin',lot:'LOT-2026-0105',cost:9200},
    {pos:'T2Ii',label:'Trasero 2 IzqI',pct:50,status:'ok',supplier:'Michelin',lot:'LOT-2026-0105',cost:9200},
    {pos:'T2Di',label:'Trasero 2 DerI',pct:45,status:'warning',supplier:'Goodyear',lot:'LOT-2026-0078',cost:8100},
    {pos:'T2D',label:'Trasero 2 Der',pct:58,status:'ok',supplier:'Goodyear',lot:'LOT-2026-0078',cost:8100}
  ]},
  {id:'6102',model:'Kenworth T880 · Eje doble',route:'Querétaro → CDMX',driver:'Ana P.',lastInspection:'2026-05-17T10:00:00',km:98500,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:95,status:'ok',supplier:'Michelin',lot:'LOT-2026-0130',cost:9200},
    {pos:'D-D',label:'Delantera Der',pct:93,status:'ok',supplier:'Michelin',lot:'LOT-2026-0130',cost:9200},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:88,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0148',cost:8500},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:85,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0148',cost:8500},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:90,status:'ok',supplier:'Michelin',lot:'LOT-2026-0130',cost:9200},
    {pos:'T1D',label:'Tractivo 1 Der',pct:87,status:'ok',supplier:'Michelin',lot:'LOT-2026-0130',cost:9200}
  ]},
  {id:'7744',model:'Peterbilt 579 · Eje triple',route:'Puebla → Veracruz',driver:'Roberto M.',lastInspection:'2026-05-16T14:20:00',km:275300,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:60,status:'ok',supplier:'Goodyear',lot:'LOT-2026-0080',cost:8100},
    {pos:'D-D',label:'Delantera Der',pct:58,status:'ok',supplier:'Goodyear',lot:'LOT-2026-0080',cost:8100},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:35,status:'warning',supplier:'Hankook',lot:'LOT-2026-0091',cost:6200},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:30,status:'warning',supplier:'Hankook',lot:'LOT-2026-0091',cost:6200},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:52,status:'ok',supplier:'Continental',lot:'LOT-2026-0118',cost:7800},
    {pos:'T1D',label:'Tractivo 1 Der',pct:55,status:'ok',supplier:'Continental',lot:'LOT-2026-0118',cost:7800},
    {pos:'T2I',label:'Trasero 2 Izq',pct:48,status:'warning',supplier:'Goodyear',lot:'LOT-2026-0082',cost:8100},
    {pos:'T2Ii',label:'Trasero 2 IzqI',pct:40,status:'warning',supplier:'Goodyear',lot:'LOT-2026-0082',cost:8100},
    {pos:'T2Di',label:'Trasero 2 DerI',pct:53,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0155',cost:8500},
    {pos:'T2D',label:'Trasero 2 Der',pct:56,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0155',cost:8500}
  ]},
  {id:'2289',model:'Volvo VNL 860 · Eje doble',route:'CDMX → Guadalajara',driver:'Laura S.',lastInspection:'2026-05-17T08:45:00',km:145600,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:85,status:'ok',supplier:'Michelin',lot:'LOT-2026-0133',cost:9200},
    {pos:'D-D',label:'Delantera Der',pct:82,status:'ok',supplier:'Michelin',lot:'LOT-2026-0133',cost:9200},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:76,status:'ok',supplier:'Continental',lot:'LOT-2026-0122',cost:7800},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:73,status:'ok',supplier:'Continental',lot:'LOT-2026-0122',cost:7800},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:78,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0152',cost:8500},
    {pos:'T1D',label:'Tractivo 1 Der',pct:80,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0152',cost:8500}
  ]},
  {id:'8856',model:'Mack Anthem · Eje triple',route:'Toluca → Morelia',driver:'Pedro G.',lastInspection:'2026-05-15T11:30:00',km:198700,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:68,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0160',cost:8500},
    {pos:'D-D',label:'Delantera Der',pct:65,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0160',cost:8500},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:52,status:'ok',supplier:'Goodyear',lot:'LOT-2026-0085',cost:8100},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:44,status:'warning',supplier:'Goodyear',lot:'LOT-2026-0085',cost:8100},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:50,status:'ok',supplier:'Hankook',lot:'LOT-2026-0093',cost:6200},
    {pos:'T1D',label:'Tractivo 1 Der',pct:56,status:'ok',supplier:'Hankook',lot:'LOT-2026-0093',cost:6200},
    {pos:'T2I',label:'Trasero 2 Izq',pct:62,status:'ok',supplier:'Continental',lot:'LOT-2026-0125',cost:7800},
    {pos:'T2Ii',label:'Trasero 2 IzqI',pct:58,status:'ok',supplier:'Continental',lot:'LOT-2026-0125',cost:7800},
    {pos:'T2Di',label:'Trasero 2 DerI',pct:60,status:'ok',supplier:'Michelin',lot:'LOT-2026-0138',cost:9200},
    {pos:'T2D',label:'Trasero 2 Der',pct:54,status:'ok',supplier:'Michelin',lot:'LOT-2026-0138',cost:9200}
  ]},
  {id:'1195',model:'Freightliner M2 · Eje doble',route:'León → Aguascalientes',driver:'Diana F.',lastInspection:'2026-05-17T06:50:00',km:67800,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:92,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0162',cost:8500},
    {pos:'D-D',label:'Delantera Der',pct:90,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0162',cost:8500},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:86,status:'ok',supplier:'Michelin',lot:'LOT-2026-0140',cost:9200},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:84,status:'ok',supplier:'Michelin',lot:'LOT-2026-0140',cost:9200},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:88,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0162',cost:8500},
    {pos:'T1D',label:'Tractivo 1 Der',pct:85,status:'ok',supplier:'Bridgestone',lot:'LOT-2026-0162',cost:8500}
  ]},
  {id:'9901',model:'Kenworth W990 · Eje triple',route:'Saltillo → Chihuahua',driver:'Fernando T.',lastInspection:'2026-05-16T09:10:00',km:340100,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:45,status:'warning',supplier:'Goodyear',lot:'LOT-2026-0076',cost:8100},
    {pos:'D-D',label:'Delantera Der',pct:42,status:'warning',supplier:'Goodyear',lot:'LOT-2026-0076',cost:8100},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:38,status:'warning',supplier:'Hankook',lot:'LOT-2026-0095',cost:6200},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:25,status:'critical',supplier:'Hankook',lot:'LOT-2026-0095',cost:6200},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:30,status:'critical',supplier:'Continental',lot:'LOT-2026-0128',cost:7800},
    {pos:'T1D',label:'Tractivo 1 Der',pct:35,status:'warning',supplier:'Continental',lot:'LOT-2026-0128',cost:7800},
    {pos:'T2I',label:'Trasero 2 Izq',pct:40,status:'warning',supplier:'Goodyear',lot:'LOT-2026-0076',cost:8100},
    {pos:'T2Ii',label:'Trasero 2 IzqI',pct:22,status:'critical',supplier:'Hankook',lot:'LOT-2026-0095',cost:6200},
    {pos:'T2Di',label:'Trasero 2 DerI',pct:33,status:'warning',supplier:'Bridgestone',lot:'LOT-2026-0165',cost:8500},
    {pos:'T2D',label:'Trasero 2 Der',pct:36,status:'warning',supplier:'Bridgestone',lot:'LOT-2026-0165',cost:8500}
  ]},
  {id:'4400',model:'International HX · Eje doble',route:'Mérida → Cancún',driver:'Sandra V.',lastInspection:'2026-05-17T11:20:00',km:112300,
   tires:[
    {pos:'D-I',label:'Delantera Izq',pct:80,status:'ok',supplier:'Continental',lot:'LOT-2026-0130',cost:7800},
    {pos:'D-D',label:'Delantera Der',pct:77,status:'ok',supplier:'Continental',lot:'LOT-2026-0130',cost:7800},
    {pos:'T1I',label:'Tractivo 1 Izq',pct:72,status:'ok',supplier:'Michelin',lot:'LOT-2026-0142',cost:9200},
    {pos:'T1Ii',label:'Tractivo 1 IzqI',pct:68,status:'ok',supplier:'Michelin',lot:'LOT-2026-0142',cost:9200},
    {pos:'T1Di',label:'Tractivo 1 DerI',pct:70,status:'ok',supplier:'Goodyear',lot:'LOT-2026-0088',cost:8100},
    {pos:'T1D',label:'Tractivo 1 Der',pct:74,status:'ok',supplier:'Goodyear',lot:'LOT-2026-0088',cost:8100}
  ]},
];

const SUPPLIERS = [
  {name:'Bridgestone',rating:92,quality:98.5,onTime:96,claimsAccepted:90,responseTime:1.8,costPerTire:8500,costPerKm:1.95,grade:'A'},
  {name:'Michelin',rating:95,quality:99.1,onTime:98,claimsAccepted:93,responseTime:1.2,costPerTire:9200,costPerKm:1.82,grade:'A'},
  {name:'Continental',rating:87,quality:97.2,onTime:92,claimsAccepted:85,responseTime:2.5,costPerTire:7800,costPerKm:2.10,grade:'B'},
  {name:'Goodyear',rating:83,quality:96.8,onTime:89,claimsAccepted:82,responseTime:3.1,costPerTire:8100,costPerKm:2.25,grade:'B'},
  {name:'Hankook',rating:72,quality:93.5,onTime:80,claimsAccepted:75,responseTime:4.2,costPerTire:6200,costPerKm:2.80,grade:'C'},
];

const LOTS = [
  {id:'LOT-2026-0162',supplier:'Bridgestone',date:'2026-05-10',qty:24,defects:0,incidents:0,trace:100,status:'ok'},
  {id:'LOT-2026-0142',supplier:'Bridgestone',date:'2026-04-28',qty:20,defects:1,incidents:0,trace:100,status:'ok'},
  {id:'LOT-2026-0140',supplier:'Michelin',date:'2026-04-25',qty:16,defects:0,incidents:0,trace:100,status:'ok'},
  {id:'LOT-2026-0133',supplier:'Michelin',date:'2026-04-20',qty:18,defects:0,incidents:0,trace:100,status:'ok'},
  {id:'LOT-2026-0130',supplier:'Continental',date:'2026-04-18',qty:22,defects:1,incidents:1,trace:95,status:'warning'},
  {id:'LOT-2026-0128',supplier:'Continental',date:'2026-04-15',qty:14,defects:2,incidents:2,trace:100,status:'critical'},
  {id:'LOT-2026-0120',supplier:'Continental',date:'2026-04-08',qty:20,defects:0,incidents:0,trace:100,status:'ok'},
  {id:'LOT-2026-0095',supplier:'Hankook',date:'2026-03-28',qty:30,defects:4,incidents:3,trace:87,status:'critical'},
  {id:'LOT-2026-0091',supplier:'Hankook',date:'2026-03-22',qty:16,defects:2,incidents:1,trace:90,status:'warning'},
  {id:'LOT-2026-0087',supplier:'Hankook',date:'2026-03-18',qty:18,defects:3,incidents:2,trace:85,status:'critical'},
  {id:'LOT-2026-0085',supplier:'Goodyear',date:'2026-03-15',qty:20,defects:1,incidents:0,trace:100,status:'ok'},
  {id:'LOT-2026-0080',supplier:'Goodyear',date:'2026-03-10',qty:24,defects:1,incidents:1,trace:96,status:'warning'},
];

const CLAIMS = [
  {id:'IES-0451',type:'auto',severity:'critical',truck:'9901',pos:'T1Ii',description:'Desgaste severo 75% — objeto incrustado detectado por IA',supplier:'Hankook',lot:'LOT-2026-0095',status:'open',date:'2026-05-17',response:null},
  {id:'IES-0450',type:'auto',severity:'critical',truck:'9901',pos:'T2Ii',description:'Vida útil 22% — grieta lateral detectada por visión IA',supplier:'Hankook',lot:'LOT-2026-0095',status:'open',date:'2026-05-17',response:null},
  {id:'IES-0449',type:'auto',severity:'critical',truck:'4821',pos:'T2Ii',description:'Vida útil 28% — baja presión + objeto incrustado',supplier:'Hankook',lot:'LOT-2026-0087',status:'open',date:'2026-05-17',response:null},
  {id:'IES-0448',type:'manual',severity:'warning',truck:'7744',pos:'T1I',description:'Desgaste desigual — posible desalineación',supplier:'Hankook',lot:'LOT-2026-0091',status:'open',date:'2026-05-16',response:null},
  {id:'IES-0447',type:'auto',severity:'warning',truck:'3917',pos:'T2Di',description:'Desgaste acelerado — 45% vida restante',supplier:'Goodyear',lot:'LOT-2026-0078',status:'open',date:'2026-05-16',response:null},
  {id:'IES-0445',type:'auto',severity:'critical',truck:'9901',pos:'T1Di',description:'Vida útil 30% — reemplazo urgente',supplier:'Continental',lot:'LOT-2026-0128',status:'resolved',date:'2026-05-15',response:1.5},
  {id:'IES-0440',type:'manual',severity:'warning',truck:'8856',pos:'T1Ii',description:'Advertencia de desgaste — 44% vida restante',supplier:'Goodyear',lot:'LOT-2026-0085',status:'resolved',date:'2026-05-14',response:2.0},
  {id:'IES-0438',type:'auto',severity:'warning',truck:'4821',pos:'T1Di',description:'Presión baja detectada por visión',supplier:'Continental',lot:'LOT-2026-0115',status:'resolved',date:'2026-05-13',response:1.8},
];

const SUPPLY_KPIS = [
  {name:'Tasa aceptación reclamos',value:'87%',unit:'%',status:'ok',cat:'quality',crit:'< 70%',warn:'70–84%',opt:'≥ 85%'},
  {name:'Tiempo resp. reclamos',value:'2.3 d',unit:'días',status:'ok',cat:'quality',crit:'> 5 d',warn:'3–5 d',opt:'< 3 d'},
  {name:'Calidad de lote',value:'96.8%',unit:'%',status:'ok',cat:'quality',crit:'< 90%',warn:'90–94%',opt:'≥ 95%'},
  {name:'Cumplimiento entregas',value:'94%',unit:'%',status:'ok',cat:'quality',crit:'< 80%',warn:'80–89%',opt:'≥ 90%'},
  {name:'Score prom. proveedores',value:'85.8',unit:'pts',status:'ok',cat:'quality',crit:'< 70',warn:'70–84',opt:'≥ 85'},
  {name:'Costo promedio/llanta',value:'$7,960',unit:'MXN',status:'warn',cat:'cost',crit:'> $10,000',warn:'$7k–$10k',opt:'< $7,000'},
  {name:'Costo por km recorrido',value:'$2.18',unit:'MXN/km',status:'ok',cat:'cost',crit:'> $3.00',warn:'$2.50–3.00',opt:'< $2.50'},
  {name:'Ciclo de suministro',value:'3.2 d',unit:'días',status:'ok',cat:'cost',crit:'> 7 d',warn:'4–7 d',opt:'< 4 d'},
  {name:'Trazabilidad completa',value:'96%',unit:'%',status:'ok',cat:'trace',crit:'< 80%',warn:'80–94%',opt:'≥ 95%'},
  {name:'Incidencias por lote',value:'0.83',unit:'avg',status:'warn',cat:'trace',crit:'> 2.0',warn:'0.5–2.0',opt:'< 0.5'},
  {name:'Eventos IES automáticos',value:'12',unit:'este mes',status:'warn',cat:'trace',crit:'> 15',warn:'5–15',opt:'< 5'},
];
