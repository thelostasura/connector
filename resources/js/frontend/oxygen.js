// controller.template.js

const oxygen = {
    addComponent(data, componentId, designSet) {
        angular.element(document.getElementById('ct-controller-ui')).scope().iframeScope.addComponentFromSource(JSON.stringify(data), false, null, designSet);
    },
    addPage(data, designSet) {
        angular.element(document.getElementById('ct-controller-ui')).scope().iframeScope.addPageFromSource(JSON.stringify(data), null, designSet);
    }
};

export default oxygen;