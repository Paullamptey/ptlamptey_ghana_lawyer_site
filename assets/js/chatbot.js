// Simple conversational UI with Ghanaian-inspired motif
const chatToggle = document.getElementById('chat-toggle');
const chatPanel  = document.querySelector('.chat-panel');
const chatLog    = document.getElementById('chat-log');
const chatInput  = document.getElementById('chat-input');
const chatSend   = document.getElementById('chat-send');

function addMsg(role, text){
  const block = document.createElement('div');
  block.className = `msg ${role}`;
  block.innerHTML = `<div>${text}</div><div class="msg-meta">${new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})} • ${role==='bot'?'Read':'Sent'}</div>`;
  chatLog.appendChild(block);
  chatLog.scrollTop = chatLog.scrollHeight;
}

function typing(on=true){
  let t = document.querySelector('.typing');
  if(on && !t){
    const el = document.createElement('div');
    el.className='typing';
    const wrap = document.createElement('div');
    wrap.className='msg bot';
    wrap.appendChild(el);
    chatLog.appendChild(wrap);
    chatLog.scrollTop = chatLog.scrollHeight;
  }else if(!on && t){
    t.parentElement.remove();
  }
}

chatToggle.addEventListener('click', ()=>{
  chatPanel.classList.toggle('open');
  if (chatPanel.classList.contains('open')) {
    // Focus input when opening
    setTimeout(()=> chatInput?.focus(), 50);
    // Seed greeting only once
    if (chatLog.children.length===0) {
      setTimeout(()=>{
        addMsg('bot', 'Akwaaba. As a qualified advocate of the Supreme Court of Ghana with international legal expertise, I provide professional counsel on Ghanaian law and global legal practices. How may I assist you today?');
      }, 150);
    }
  }
});

chatSend?.addEventListener('click', handleSend);
chatInput?.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ handleSend(); } });

// Delegated click handler as a safety net (helps when precise button center isn't clicked)
document.addEventListener('click', (e)=>{
  const tgt = e.target;
  if (tgt && (tgt.id === 'chat-send' || tgt.closest?.('#chat-send'))) {
    handleSend();
  }
});

// Always focus input when clicking anywhere inside the chat panel (except the input itself)
chatPanel?.addEventListener('click', (e)=>{
  if ((e.target)?.id !== 'chat-input') {
    chatInput?.focus();
  }
});

// Global Enter handler while panel is open (ignores Shift+Enter). Safe because handleSend() no-ops on empty input.
document.addEventListener('keydown', (e)=>{
  if (e.key === 'Enter' && !e.shiftKey && chatPanel?.classList.contains('open')) {
    // If focus isn't in chat input, move it there first
    if (document.activeElement !== chatInput) {
      chatInput?.focus();
    }
    // Try to send (will return if input is empty)
    handleSend();
    // Prevent form submits behind the panel
    e.preventDefault();
  }
});

async function handleSend(){
  const text = chatInput.value.trim();
  if(!text) return;
  console.log('[chat] handleSend:', text);
  addMsg('user', text);
  chatInput.value = '';
  chatInput.focus();

  // Try local routing first; if handled, skip server call
  try {
    const handled = route(text);
    if (handled === true) { return; }
  } catch (_) {
    // ignore routing errors and proceed to server
  }

  typing(true);
  try {
    const response = await fetch('server/handlers/chatbot.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept':'application/json' },
      body: JSON.stringify({ message: text })
    });
    const ct = response.headers.get('content-type') || '';
    let data;
    if (ct.includes('application/json')) {
      data = await response.json();
    } else {
      // fallback: try text
      const txt = await response.text();
      data = { response: txt };
    }
    typing(false);
    addMsg('bot', data.response || 'Sorry, I could not process your request.');
  } catch (error) {
    typing(false);
    addMsg('bot', 'Network error. Please try again.');
  }
}

// Professional legal routing with global legal knowledge
function route(text){
  const t = text.toLowerCase();

  // Ghana-specific practices
  if(/family|marriage|custody|maintenance|adoption/.test(t)){
    addMsg('bot', 'Family Law: As a qualified advocate in Ghana, I specialize in customary and statutory marriages, custody disputes, maintenance orders, and adoption proceedings under the Children’s Act 1998 and Matrimonial Causes Act 1971. This includes navigating both ordinance and customary law frameworks. Would you like to schedule a consultation? Type: appointment [date] [time].');
    return true;
  }
  if(/property|land|title|conveyance|conveyancing/.test(t)){
    addMsg('bot', 'Property & Land Law: I handle title verification, conveyancing, land litigation, and compliance with the Land Act 2020 and Lands Commission procedures. This involves official searches, deed preparation, and registration processes. To discuss your case, type: appointment [date] [time].');
    return true;
  }
  if(/immigration|permit|residence|citizenship|gis/.test(t)){
    addMsg('bot', 'Immigration Law: I assist with residence permits, citizenship applications, and compliance under Ghana Immigration Service regulations. This includes visa extensions, work permits, and naturalization processes. Ready to proceed? Type: appointment [date] [time].');
    return true;
  }
  if(/company|corporate|roc|compliance|contract/.test(t)){
    addMsg('bot', 'Corporate & Commercial Law: Services include company formation, regulatory filings with the Registrar of Companies, data protection compliance under the Data Protection Act 2012, and contract drafting/review. For business legal needs, type: appointment [date] [time].');
    return true;
  }
  if(/criminal|bail|charge|trial/.test(t)){
    addMsg('bot', 'Criminal Law: I provide defense representation from charge to trial, including bail applications and appeals, ensuring constitutional fair trial rights under the 1992 Constitution. For criminal matters, type: appointment [date] [time].');
    return true;
  }

  // General legal practices (international knowledge)
  if(/contract|breach|agreement/.test(t)){
    addMsg('bot', 'Contract Law: Contracts are fundamental to business and personal dealings. I advise on formation, interpretation, performance, and remedies for breach. This includes common law principles, statutory requirements, and dispute resolution. Applicable globally with jurisdiction-specific nuances.');
    return true;
  }
  if(/tort|negligence|injury|damages/.test(t)){
    addMsg('bot', 'Tort Law: Covering negligence, intentional torts, and strict liability. I handle personal injury claims, professional negligence, and defamation cases. Remedies include compensatory and punitive damages, following principles from Donoghue v Stevenson and local statutes.');
    return true;
  }
  if(/employment|labor|workplace|dismissal/.test(t)){
    addMsg('bot', 'Employment Law: Advising on contracts, unfair dismissal, discrimination, and workplace rights. In Ghana, this aligns with the Labour Act 2003 and international labor standards. Globally, I understand varying jurisdictions from EU directives to US federal laws.');
    return true;
  }
  if(/intellectual|property|patent|trademark|copyright/.test(t)){
    addMsg('bot', 'Intellectual Property Law: Protection of patents, trademarks, copyrights, and trade secrets. I assist with registration, enforcement, and licensing under the Copyright Act 2005, Trademarks Act 2004, and international treaties like TRIPS.');
    return true;
  }
  if(/international|trade|commerce|wto|investment/.test(t)){
    addMsg('bot', 'International Trade & Investment Law: Navigating WTO agreements, bilateral investment treaties, and foreign investment regulations. I advise on cross-border transactions, sanctions compliance, and dispute resolution through ICSID or UNCITRAL.');
    return true;
  }
  if(/environmental|climate|pollution|sustainability/.test(t)){
    addMsg('bot', 'Environmental Law: Compliance with national environmental acts and international conventions like the Paris Agreement. I handle permitting, liability for pollution, and sustainable development projects.');
    return true;
  }
  if(/human rights|constitutional|fundamental/.test(t)){
    addMsg('bot', 'Human Rights & Constitutional Law: Protecting fundamental rights under the 1992 Constitution and international covenants. I litigate on freedom of expression, due process, and equality claims, drawing from global jurisprudence.');
    return true;
  }
  if(/tax|revenue|income|corporate tax/.test(t)){
    addMsg('bot', 'Tax Law: Advising on income tax, corporate tax, VAT, and international tax treaties. In Ghana, this involves the Income Tax Act 2015 and Revenue Administration Act. Globally, I understand OECD guidelines and tax optimization strategies.');
    return true;
  }
  if(/bankruptcy|insolvency|debt|creditor/.test(t)){
    addMsg('bot', 'Insolvency & Bankruptcy Law: Handling corporate and personal insolvency under the Insolvency Act 2018. This includes restructuring, liquidation, and creditor rights, with knowledge of international insolvency frameworks.');
    return true;
  }
  if(/arbitration|mediation|adr|dispute/.test(t)){
    addMsg('bot', 'Alternative Dispute Resolution: Mediation and arbitration services for efficient conflict resolution. I facilitate ADR under Ghana’s ADR Act 2010 and international rules like UNCITRAL Model Law.');
    return true;
  }

  // Appointment intent
  const appMatch = t.match(/appointment\s+(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})/);
  if(appMatch){
    const [_, date, time] = appMatch;
    addMsg('bot', `Consultation request noted for ${date} at ${time}. As a professional advocate, I ensure all discussions remain confidential and privileged. You’ll receive confirmation via email and SMS shortly.`);
    fetch('server/handlers/submit_appointment.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ name:'Chat User', email:'chat@local', phone:'N/A', type:'General', date, time })
    }).catch(()=>{});
    return true;
  }

  // Complaint intent
  if(/complaint|report|issue|feedback/.test(t)){
    addMsg('bot', 'Professional conduct complaints are taken seriously. Please provide details in the format: complaint [category: conduct|billing|delay|other] [urgency: low|medium|high] [description]. All matters are handled in accordance with Bar Association ethical standards.');
    return true;
  }
  const compMatch = t.match(/complaint\s+(\w+)\s+(low|medium|high)\s+(.+)/);
  if(compMatch){
    const [_, category, urgency, details] = compMatch;
    addMsg('bot', 'Complaint acknowledged. As per professional regulations, we will investigate promptly and respond within 14 days. Confidentiality is assured.');
    fetch('server/handlers/submit_complaint.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ name:'Chat User', email:'chat@local', phone:'N/A', category, urgency, details })
    }).catch(()=>{});
    return true;
  }

  // FAQ
  if(/land commission|lands commission/.test(t)){
    addMsg('bot', 'Lands Commission: As registered practitioners, we conduct official title searches, verify parcel interests, and prepare documentation for stamping and registration under the Land Title Registration Act 1986.');
    return true;
  }
  if(/fees|cost|charge|pricing/.test(t)){
    addMsg('bot', 'Fee Structure: Transparent billing in accordance with the Legal Profession (Professional Conduct and Etiquette) Rules 2020. We provide detailed fee quotes and scope letters. Initial consultations are charged at standard rates; complex matters require retainers.');
    return true;
  }
  if(/how to book|book|schedule/.test(t)){
    addMsg('bot', 'Booking Process: Appointments can be made via this form or by typing: appointment YYYY-MM-DD HH:MM. We offer in-person consultations in Accra and virtual meetings nationwide.');
    return true;
  }
  if(/qualifications|experience|credentials/.test(t)){
    addMsg('bot', 'Credentials: Qualified Advocate & Solicitor of the Supreme Court of Ghana, member of the Ghana Bar Association. Over 15 years of practice in constitutional, commercial, and customary law, with international arbitration experience.');
    return true;
  }

  // Fallback: not handled locally
  return false;
}