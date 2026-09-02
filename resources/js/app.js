const mobileMenuButton = document.getElementById('mobileMenuButton');
const navLinks = document.getElementById('navLinks');

if (mobileMenuButton && navLinks) {
    mobileMenuButton.addEventListener('click', () => {
        navLinks.classList.toggle('open');
    });
}

const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';

        passwordInput.type = isPassword ? 'text' : 'password';
        togglePassword.textContent = isPassword ? 'Hide' : 'Show';
    });
}

const sidebarToggle = document.getElementById('sidebarToggle');
const portalSidebar = document.getElementById('portalSidebar');

if (sidebarToggle && portalSidebar) {
    sidebarToggle.addEventListener('click', () => {
        portalSidebar.classList.toggle('open');
    });
}

document.querySelectorAll('[data-modal-open]').forEach((button) => {
    button.addEventListener('click', () => document.getElementById(button.dataset.modalOpen)?.showModal());
});
document.querySelectorAll('[data-modal-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
});

const cmsPreview = document.getElementById('cmsPreview');
if (cmsPreview) {
    cmsPreview.addEventListener('click', () => {
        document.getElementById('previewEn').textContent = document.getElementById('contentEn').value || 'English preview';
        document.getElementById('previewBn').textContent = document.getElementById('contentBn').value || 'বাংলা প্রিভিউ';
        document.getElementById('cmsPreviewModal').showModal();
    });
}

const landWizard = document.getElementById('landWizard');
if (landWizard) {
    let currentStep = 1;
    const panels = [...landWizard.querySelectorAll('[data-wizard-panel]')];
    const indicators = [...document.querySelectorAll('[data-wizard-step]')];
    const back = document.getElementById('wizardBack');
    const next = document.getElementById('wizardNext');
    const submit = document.getElementById('wizardSubmit');
    const showStep = (step) => {
        currentStep = step;
        panels.forEach((panel) => panel.classList.toggle('active', Number(panel.dataset.wizardPanel) === step));
        indicators.forEach((item) => { const value=Number(item.dataset.wizardStep); item.classList.toggle('active',value===step); item.classList.toggle('done',value<step); });
        back.hidden = step === 1; next.hidden = step === 3; submit.hidden = step !== 3;
        if (step === 3) {
            const fields = [['Owner','owner_name'],['Phone','phone'],['Division','division'],['District','district'],['Location','location'],['Land size','katha'],['Road width','road_width'],['NID','nid_number']];
            const review = document.getElementById('landReview'); review.replaceChildren();
            fields.forEach(([label,name]) => { const row=document.createElement('div'); const key=document.createElement('span'); const value=document.createElement('b'); key.textContent=label; value.textContent=(landWizard.elements[name]?.value||'—')+(name==='katha'?' Katha':name==='road_width'?' ft':''); row.append(key,value); review.append(row); });
        }
    };
    next.addEventListener('click', () => { const invalid=[...panels[currentStep-1].querySelectorAll('input,select,textarea')].find(field=>!field.checkValidity()); if(invalid){invalid.reportValidity();return;} showStep(Math.min(3,currentStep+1)); });
    back.addEventListener('click',()=>showStep(Math.max(1,currentStep-1)));
    indicators.forEach(item=>item.addEventListener('click',()=>{const target=Number(item.dataset.wizardStep);if(target<currentStep)showStep(target);}));
}
