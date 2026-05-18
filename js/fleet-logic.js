// ══ Truck Aptitude Logic (matches TyreSense status) ══
function getTruckStatus(truck){
  const hasCrit = truck.tires.some(t=>t.status==='critical');
  if(hasCrit) return 'blocked';
  const hasWarnLow = truck.tires.some(t=>t.status==='warning'&&t.pct<40);
  if(hasWarnLow) return 'conditioned';
  const warnCount = truck.tires.filter(t=>t.status==='warning').length;
  if(warnCount>=3) return 'conditioned';
  return 'ok';
}
function statusLabel(s){return s==='ok'?'APTO':s==='conditioned'?'CONDICIONADO':'NO APTO'}
function statusBadge(s){return s==='ok'?'badge-ok':s==='conditioned'?'badge-warn':'badge-danger'}
function avgLife(tires){return Math.round(tires.reduce((a,t)=>a+t.pct,0)/tires.length)}

// ══ Navigation ══
function showSection(id){
  document.querySelectorAll('.section').forEach(s=>s.classList.add('hidden'));
  document.getElementById('section-'+id).classList.remove('hidden');
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  document.querySelector('[data-section="'+id+'"]').classList.add('active');
  const titles={fleet:['Fleet Overview','Estado de la flota y aptitud para viaje'],suppliers:['Scorecard Proveedores','Calidad, entregas y desempeño'],costs:['Costos y Eficiencia','Análisis comparativo por proveedor'],traceability:['Trazabilidad','Registro de lotes e incidencias'],claims:['Reclamos IES','Eventos generados desde detección de daño'],camera:['Inspección Manual','Captura y análisis fotográfico de llantas mediante IA'],kpis:['KPIs Cadena de Suministro','Métricas de calidad, costos y trazabilidad']};
  document.getElementById('page-title').textContent=titles[id][0];
  document.getElementById('page-subtitle').textContent=titles[id][1];
}

// ══ Fleet Rendering ══
function buildTireMap(tires){
  const cls=s=>s==='ok'?'t-ok':s==='warning'?'t-warn':'t-crit';
  // group by axle: first 2=front, next 4=drive, next 4=rear
  let html='<div class="tire-map-mini">';
  // Front axle
  html+='<div class="axle-row">';
  tires.slice(0,2).forEach(t=>{html+='<div class="tire-dot '+cls(t.status)+'" title="'+t.pos+' '+t.pct+'%"></div>'});
  html+='</div>';
  // Drive axle (if exists)
  if(tires.length>2){
    html+='<div class="axle-row">';
    tires.slice(2,Math.min(6,tires.length)).forEach(t=>{html+='<div class="tire-dot '+cls(t.status)+'" title="'+t.pos+' '+t.pct+'%"></div>'});
    html+='</div>';
  }
  // Rear axle (if exists)
  if(tires.length>6){
    html+='<div class="axle-row">';
    tires.slice(6).forEach(t=>{html+='<div class="tire-dot '+cls(t.status)+'" title="'+t.pos+' '+t.pct+'%"></div>'});
    html+='</div>';
  }
  html+='</div>';
  return html;
}

function renderFleet(filter){
  const grid=document.getElementById('fleet-grid');
  grid.innerHTML='';
  FLEET.forEach(truck=>{
    const st=getTruckStatus(truck);
    if(filter&&filter!=='all'&&st!==filter) return;
    const okC=truck.tires.filter(t=>t.status==='ok').length;
    const wC=truck.tires.filter(t=>t.status==='warning').length;
    const cC=truck.tires.filter(t=>t.status==='critical').length;
    const card=document.createElement('div');
    card.className='truck-card status-'+st;
    card.onclick=()=>openTruckModal(truck);
    card.innerHTML=`
      <div class="truck-header">
        <div><div class="truck-id">#${truck.id}</div><div class="truck-model">${truck.model}</div></div>
        <span class="truck-badge ${statusBadge(st)}">${statusLabel(st)}</span>
      </div>
      <div style="font-size:11px;color:var(--text-3);margin-bottom:4px">${truck.route} · ${truck.driver}</div>
      ${buildTireMap(truck.tires)}
      <div class="truck-stats">
        <div class="truck-stat"><div class="truck-stat-val" style="color:var(--ok)">${okC}</div><div class="truck-stat-lbl">OK</div></div>
        <div class="truck-stat"><div class="truck-stat-val" style="color:var(--warn)">${wC}</div><div class="truck-stat-lbl">Advertencia</div></div>
        <div class="truck-stat"><div class="truck-stat-val" style="color:var(--danger)">${cC}</div><div class="truck-stat-lbl">Crítico</div></div>
      </div>
      <div style="margin-top:10px;display:flex;justify-content:space-between;font-size:10px;color:var(--text-3)">
        <span>Vida prom: <strong style="color:var(--text)">${avgLife(truck.tires)}%</strong></span>
        <span>${truck.km.toLocaleString()} km</span>
      </div>`;
    grid.appendChild(card);
  });
  // Update summary counts
  const counts={ok:0,conditioned:0,blocked:0};
  FLEET.forEach(t=>{counts[getTruckStatus(t)]++});
  document.getElementById('fleet-ok').textContent=counts.ok;
  document.getElementById('fleet-warn').textContent=counts.conditioned;
  document.getElementById('fleet-crit').textContent=counts.blocked;
  document.getElementById('fleet-total').textContent=FLEET.length;
}

function filterFleet(f,btn){
  document.querySelectorAll('#section-fleet .pill').forEach(p=>p.classList.remove('active'));
  btn.classList.add('active');
  renderFleet(f);
}

// ══ Truck Modal ══
function openTruckModal(truck){
  const st=getTruckStatus(truck);
  const modal=document.getElementById('truck-modal');
  const content=document.getElementById('modal-content');
  const cls=s=>s==='ok'?'mt-ok':s==='warning'?'mt-warn':'mt-crit';
  
  let tiresHTML='<div class="modal-tire-section">';
  // Front
  tiresHTML+='<div class="modal-axle-label">Eje delantero</div><div class="modal-tire-row">';
  truck.tires.slice(0,2).forEach(t=>{tiresHTML+=`<div class="modal-tire ${cls(t.status)}"><div class="modal-tire-pos">${t.pos}</div><div class="modal-tire-pct">${t.pct}%</div><div style="font-size:8px;margin-top:2px">${t.supplier}</div></div>`});
  tiresHTML+='</div>';
  // Drive
  if(truck.tires.length>2){
    tiresHTML+='<div class="modal-axle-label">Eje tractivo</div><div class="modal-tire-row">';
    truck.tires.slice(2,Math.min(6,truck.tires.length)).forEach(t=>{tiresHTML+=`<div class="modal-tire ${cls(t.status)}"><div class="modal-tire-pos">${t.pos}</div><div class="modal-tire-pct">${t.pct}%</div><div style="font-size:8px;margin-top:2px">${t.supplier}</div></div>`});
    tiresHTML+='</div>';
  }
  // Rear
  if(truck.tires.length>6){
    tiresHTML+='<div class="modal-axle-label">Eje trasero</div><div class="modal-tire-row">';
    truck.tires.slice(6).forEach(t=>{tiresHTML+=`<div class="modal-tire ${cls(t.status)}"><div class="modal-tire-pos">${t.pos}</div><div class="modal-tire-pct">${t.pct}%</div><div style="font-size:8px;margin-top:2px">${t.supplier}</div></div>`});
    tiresHTML+='</div>';
  }
  tiresHTML+='</div>';

  // Warnings list
  const issues=truck.tires.filter(t=>t.status!=='ok');
  let issuesHTML='';
  if(issues.length){
    issuesHTML='<div style="margin-top:16px"><div style="font-size:10px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Llantas con alerta</div>';
    issues.forEach(t=>{
      const bg=t.status==='critical'?'var(--danger-bg)':'var(--warn-bg)';
      const co=t.status==='critical'?'var(--danger)':'var(--warn)';
      issuesHTML+=`<div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:${bg};border-radius:8px;margin-bottom:6px;font-size:12px;color:${co}">
        <strong>${t.pos}</strong> — ${t.pct}% vida · ${t.supplier} · ${t.lot}
        ${t.status==='critical'?'<span style="margin-left:auto;font-weight:700">⚠ REEMPLAZAR</span>':''}
      </div>`;
    });
    issuesHTML+='</div>';
  }

  content.innerHTML=`
    <div class="modal-title">Unidad #${truck.id} <span class="truck-badge ${statusBadge(st)}" style="font-size:11px;vertical-align:middle;margin-left:8px">${statusLabel(st)}</span></div>
    <div class="modal-sub">${truck.model} · ${truck.route} · ${truck.driver}</div>
    ${tiresHTML}
    <div class="modal-detail-grid">
      <div class="modal-detail"><div class="modal-detail-label">Vida promedio</div><div class="modal-detail-value">${avgLife(truck.tires)}%</div></div>
      <div class="modal-detail"><div class="modal-detail-label">Kilometraje</div><div class="modal-detail-value">${truck.km.toLocaleString()}</div></div>
      <div class="modal-detail"><div class="modal-detail-label">Total llantas</div><div class="modal-detail-value">${truck.tires.length}</div></div>
      <div class="modal-detail"><div class="modal-detail-label">Última inspección</div><div class="modal-detail-value" style="font-size:13px">${new Date(truck.lastInspection).toLocaleString('es-MX',{dateStyle:'short',timeStyle:'short'})}</div></div>
    </div>
    ${issuesHTML}`;
  modal.classList.remove('hidden');
}
function closeModal(){document.getElementById('truck-modal').classList.add('hidden')}

// ══ Suppliers ══
function renderSuppliers(){
  const tbody=document.getElementById('suppliers-tbody');
  tbody.innerHTML='';
  SUPPLIERS.forEach(s=>{
    const gCls='grade grade-'+s.grade.toLowerCase();
    const rColor=s.rating>=90?'var(--ok)':s.rating>=80?'var(--cyan)':s.rating>=70?'var(--warn)':'var(--danger)';
    tbody.innerHTML+=`<tr>
      <td><strong>${s.name}</strong></td>
      <td><div class="rating-bar"><div class="rating-fill" style="width:${s.rating}%;background:${rColor}"></div></div>${s.rating}</td>
      <td>${s.quality}%</td>
      <td>${s.onTime}%</td>
      <td>${s.claimsAccepted}%</td>
      <td>${s.responseTime} días</td>
      <td style="font-weight:700;font-family:var(--mono)">${s.rating}</td>
      <td><span class="${gCls}">${s.grade}</span></td>
    </tr>`;
  });
}

// ══ Costs ══
function renderCosts(){
  const grid=document.getElementById('costs-grid');
  grid.innerHTML='';
  // Cost per tire
  let card1='<div class="cost-card"><div class="cost-card-header"><div class="cost-card-title">Costo por Llanta (MXN)</div></div><div class="cost-bar-group">';
  const maxCost=Math.max(...SUPPLIERS.map(s=>s.costPerTire));
  SUPPLIERS.forEach(s=>{
    const pct=Math.round(s.costPerTire/maxCost*100);
    const col=s.costPerTire<=7000?'var(--ok)':s.costPerTire<=8500?'var(--cyan)':'var(--warn)';
    card1+=`<div class="cost-bar-item"><div class="cost-bar-label">${s.name}</div><div class="cost-bar-track"><div class="cost-bar-fill" style="width:${pct}%;background:${col}"></div></div><div class="cost-bar-val">$${s.costPerTire.toLocaleString()}</div></div>`;
  });
  card1+='</div></div>';
  // Cost per km
  let card2='<div class="cost-card"><div class="cost-card-header"><div class="cost-card-title">Costo por KM (MXN)</div></div><div class="cost-bar-group">';
  const maxKm=Math.max(...SUPPLIERS.map(s=>s.costPerKm));
  SUPPLIERS.forEach(s=>{
    const pct=Math.round(s.costPerKm/maxKm*100);
    const col=s.costPerKm<=2.0?'var(--ok)':s.costPerKm<=2.5?'var(--cyan)':'var(--warn)';
    card2+=`<div class="cost-bar-item"><div class="cost-bar-label">${s.name}</div><div class="cost-bar-track"><div class="cost-bar-fill" style="width:${pct}%;background:${col}"></div></div><div class="cost-bar-val">$${s.costPerKm.toFixed(2)}</div></div>`;
  });
  card2+='</div></div>';
  // Cycle time
  let card3='<div class="cost-card"><div class="cost-card-header"><div class="cost-card-title">Ciclo de Suministro</div></div>';
  card3+='<div style="text-align:center;padding:20px 0"><div style="font-size:48px;font-weight:800;font-family:var(--mono);color:var(--ok)">3.2</div><div style="font-size:13px;color:var(--text-3)">días promedio pedido→entrega</div></div>';
  card3+='<div class="cost-bar-group">';
  [{n:'Bridgestone',v:2.5},{n:'Michelin',v:3.0},{n:'Continental',v:3.5},{n:'Goodyear',v:4.0},{n:'Hankook',v:5.2}].forEach(s=>{
    const col=s.v<=3?'var(--ok)':s.v<=4?'var(--cyan)':'var(--warn)';
    card3+=`<div class="cost-bar-item"><div class="cost-bar-label">${s.n}</div><div class="cost-bar-track"><div class="cost-bar-fill" style="width:${Math.round(s.v/6*100)}%;background:${col}"></div></div><div class="cost-bar-val">${s.v} días</div></div>`;
  });
  card3+='</div></div>';
  grid.innerHTML=card1+card2+card3;
}

// ══ Traceability ══
function renderLots(){
  const tbody=document.getElementById('lots-tbody');
  tbody.innerHTML='';
  LOTS.forEach(l=>{
    const sCls=l.status==='ok'?'badge-ok':l.status==='warning'?'badge-warn':'badge-danger';
    const sLbl=l.status==='ok'?'OK':l.status==='warning'?'Alerta':'Crítico';
    const tCol=l.trace>=95?'var(--ok)':l.trace>=85?'var(--warn)':'var(--danger)';
    tbody.innerHTML+=`<tr>
      <td><strong style="font-family:var(--mono)">${l.id}</strong></td>
      <td>${l.supplier}</td>
      <td>${l.date}</td>
      <td>${l.qty}</td>
      <td>${l.defects}</td>
      <td>${l.incidents}</td>
      <td><span style="color:${tCol};font-weight:600">${l.trace}%</span></td>
      <td><span class="truck-badge ${sCls}">${sLbl}</span></td>
    </tr>`;
  });
}

// ══ Claims ══
function renderClaims(){
  const list=document.getElementById('claims-list');
  list.innerHTML='';
  CLAIMS.forEach(c=>{
    const iCls=c.severity==='critical'?'ci-crit':c.severity==='warning'?'ci-warn':'ci-ok';
    const icon=c.severity==='critical'?'⚠':'⚡';
    const tagCls=c.status==='open'?'badge-danger':'badge-ok';
    const tagLbl=c.status==='open'?'Abierto':'Resuelto';
    const typeLbl=c.type==='auto'?'🤖 Auto-IA':'✋ Manual';
    list.innerHTML+=`<div class="claim-card">
      <div class="claim-icon ${iCls}">${icon}</div>
      <div class="claim-body">
        <div class="claim-title">${c.id} — Unidad #${c.truck} · Pos ${c.pos}</div>
        <div class="claim-sub">${c.description}</div>
        <div style="font-size:10px;color:var(--text-3);margin-top:4px">${c.supplier} · ${c.lot} · ${typeLbl}</div>
      </div>
      <div class="claim-meta">
        <span class="claim-tag truck-badge ${tagCls}">${tagLbl}</span>
        <span class="claim-time">${c.date}</span>
      </div>
    </div>`;
  });
}

// ══ KPIs ══
function renderKPIs(cat){
  const grid=document.getElementById('kpi-grid');
  grid.innerHTML='';
  const filtered=cat&&cat!=='all'?SUPPLY_KPIS.filter(k=>k.cat===cat):SUPPLY_KPIS;
  filtered.forEach(k=>{
    const cardCls=k.status==='ok'?'kpi-ok':k.status==='warn'?'kpi-warn':'kpi-crit';
    const dotCls=k.status==='ok'?'s-ok':k.status==='warn'?'s-warn':'s-crit';
    const vColor=k.status==='ok'?'var(--ok)':k.status==='warn'?'var(--warn)':'var(--danger)';
    grid.innerHTML+=`<div class="kpi-card ${cardCls}">
      <div class="kpi-header"><div class="kpi-name">${k.name}</div><div class="kpi-status ${dotCls}"></div></div>
      <div class="kpi-value" style="color:${vColor}">${k.value}</div>
      <div class="kpi-unit">${k.unit}</div>
      <div class="kpi-ranges">
        <div class="kpi-range"><div class="kpi-range-dot rd-crit"></div>${k.crit}</div>
        <div class="kpi-range"><div class="kpi-range-dot rd-warn"></div>${k.warn}</div>
        <div class="kpi-range"><div class="kpi-range-dot rd-ok"></div>${k.opt}</div>
      </div>
    </div>`;
  });
}

function filterKPIs(cat,btn){
  document.querySelectorAll('#section-kpis .pill').forEach(p=>p.classList.remove('active'));
  btn.classList.add('active');
  renderKPIs(cat);
}

// ══ AI Simulation ══
async function simulateAIInspection(){
  const btn = document.getElementById('btn-simulate');
  const ogText = btn.innerHTML;
  btn.innerHTML = '<span class="live-dot" style="background:white"></span> Analizando...';
  btn.disabled = true;

  try {
    const res = await fetch('http://localhost:8000/api/inspect_tire', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({truck_id: '9901'})
    });
    
    if(!res.ok) throw new Error('API Error');
    const data = await res.json();
    
    // Inject the new claim at the top
    if(data.supply_chain && data.supply_chain.claim_action.status === 'claim_generated'){
      const claim = data.supply_chain.claim_action.claim;
      const newClaim = {
        id: claim.id,
        type: claim.type,
        severity: claim.severity,
        truck: claim.truck_id,
        pos: claim.pos,
        description: claim.description,
        supplier: claim.supplier,
        lot: claim.lot,
        status: 'open',
        date: new Date().toLocaleDateString('es-MX'),
        response: null
      };
      CLAIMS.unshift(newClaim);
      renderClaims();
      
      // If we are currently on claims tab or kpis tab, briefly highlight the new element
      alert(`¡Nueva anomalía detectada por IA! Reclamo ${claim.id} generado.`);
    } else {
      alert(`Inspección completada por IA: Llanta ${data.vision.pos} se encuentra en estado ${data.vision.severity.toUpperCase()}`);
    }
  } catch (err) {
    console.error(err);
    alert("Error al simular inspección. Asegúrate de que el orquestador esté corriendo en localhost:8000");
  } finally {
    btn.innerHTML = ogText;
    btn.disabled = false;
  }
}

// ══ Clock ══
function updateClock(){
  const now=new Date();
  document.getElementById('current-time').textContent=now.toLocaleDateString('es-MX',{day:'2-digit',month:'short',year:'numeric'}).toUpperCase()+' · '+now.toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'});
}

// ══ Close modal on overlay click ══
document.getElementById('truck-modal').addEventListener('click',function(e){if(e.target===this)closeModal()});

// ══ Init ══
renderFleet('all');
renderSuppliers();
renderCosts();
renderLots();
renderClaims();
renderKPIs('all');
updateClock();
setInterval(updateClock,60000);

// ══ Camera / File Upload Logic ══
let selectedImageFile = null;

function handleImageSelect(event) {
  const file = event.target.files[0];
  if (!file) return;
  selectedImageFile = file;
  
  const reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('image-preview').src = e.target.result;
    document.getElementById('image-preview-container').style.display = 'block';
    document.getElementById('btn-analyze').style.display = 'block';
    document.getElementById('analysis-results').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

async function analyzeImage() {
  if (!selectedImageFile) {
    alert("Por favor selecciona o toma una foto primero.");
    return;
  }
  
  const btn = document.getElementById('btn-analyze');
  const loading = document.getElementById('analysis-loading');
  const resultsDiv = document.getElementById('analysis-results');
  const resultsContent = document.getElementById('results-content');
  
  btn.disabled = true;
  loading.style.display = 'block';
  resultsDiv.style.display = 'none';
  
  try {
    const formData = new FormData();
    formData.append('file', selectedImageFile);
    formData.append('truck_id', '9901');
    
    const res = await fetch('http://localhost:8000/api/upload_and_inspect', {
      method: 'POST',
      body: formData
    });
    
    if (!res.ok) throw new Error('API Error: ' + res.status);
    const data = await res.json();
    
    const v = data.vision;
    const sColor = v.severity === 'critical' ? 'var(--danger)' : v.severity === 'warning' ? 'var(--warn)' : 'var(--ok)';
    
    let html = `
      <div style="margin-bottom:15px; padding:12px; background:rgba(0,0,0,0.2); border-radius:8px;">
        <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase;">Evaluación IA Visual</div>
        <div style="font-size:18px; font-weight:bold; color:${sColor}; margin-top:5px;">
          ${v.status.toUpperCase()} (${v.confidence}%)
        </div>
        <div style="font-size:14px; margin-top:5px;">
          Clase detectada: <strong>${v.class_name}</strong>
        </div>
      </div>
      
      <div style="margin-bottom:15px; padding:12px; background:rgba(0,0,0,0.2); border-radius:8px;">
        <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase;">Impacto en Flota (Unidad #9901)</div>
        <div style="font-size:16px; font-weight:bold; margin-top:5px;">
          Estado: ${data.truck_health.verdict.toUpperCase()}
        </div>
        <div style="font-size:13px; margin-top:5px; color:var(--text-muted);">
          Razón: ${data.truck_health.reasons[0] || 'Ninguna'}
        </div>
      </div>
    `;
    
    if (data.supply_chain.claim_action.status === 'claim_generated') {
      const claim = data.supply_chain.claim_action.claim;
      html += `
        <div style="padding:12px; background:rgba(239, 68, 68, 0.1); border-left:4px solid var(--danger); border-radius:4px;">
          <div style="font-size:12px; color:var(--danger); font-weight:bold;">RECLAMO IES GENERADO AUTOMÁTICAMENTE</div>
          <div style="font-size:14px; margin-top:5px;">ID: ${claim.id} | Proveedor: ${claim.supplier}</div>
          <div style="font-size:13px; margin-top:5px;">Lote: ${claim.lot}</div>
        </div>
      `;
    } else {
       html += `
        <div style="padding:12px; background:rgba(16, 185, 129, 0.1); border-left:4px solid var(--ok); border-radius:4px;">
          <div style="font-size:12px; color:var(--ok); font-weight:bold;">SIN ACCIÓN EN CADENA DE SUMINISTRO</div>
          <div style="font-size:13px; margin-top:5px;">No se requiere reclamo IES para este estado.</div>
        </div>
      `;
    }
    
    resultsContent.innerHTML = html;
    resultsDiv.style.display = 'block';
    
  } catch (err) {
    console.error(err);
    alert('Error al analizar la imagen. Verifica que el servidor de IA esté en ejecución en el puerto 8000.');
  } finally {
    loading.style.display = 'none';
    btn.disabled = false;
  }
}

