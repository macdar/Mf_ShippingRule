<?php

class Mf_ShippingRule_Adminhtml_ShippingruleController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _getHelper()
    {
        return Mage::helper('mf_shippingrule');
    }
    
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('mf_shippingrule')
            ->_addBreadcrumb(
                $this->_getHelper()->__('Shipping Rules'), 
                $this->_getHelper()->__('Shipping Rules')
            );
        return $this;
    }
    
    public function indexAction()
    {
        $this->_title($this->__('Shipping Rules'))->_title($this->__('Manage Rules'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_initAction()
            ->_addBreadcrumb(
                $this->_getHelper()->__('Manage Rules'), 
                $this->_getHelper()->__('Manage Rules')
            );

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mf_shippingrule/rule');
        
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()->addError(
                    $this->_getHelper()->__('This rule no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }
        $this->_title($id ? $model->getName() : $this->__('New Rule'));
        
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        } 
        Mage::register('rule_data', $model);
        
        $this->_initAction()
            ->_addBreadcrumb(
                $this->_getHelper()->__('Manage Rules'), 
                $this->_getHelper()->__('Manage Rules')
            )
            ->_addBreadcrumb(
                $id ? $this->_getHelper()->__('Edit Rule')
                    : $this->_getHelper()->__('New Rule'),
                $id ? $this->_getHelper()->__('Edit Rule')
                    : $this->_getHelper()->__('New Rule')
            );
        $this->renderLayout();   
    }

    public function saveAction()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $data = $this->getRequest()->getParam('rule');

        if ($data) {
            try {
                $model = Mage::getModel('mf_shippingrule/rule');
                $model->load($id);
                $model->loadPost($data);
                $this->_getSession()->setFormData($data);
                $model->save();
                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('Rule was successfully saved.')
                );
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array(
                        'id' => $model->getId(),
                    ));
                    return;
                }
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }

        $this->_redirect('*/*');
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $model = Mage::getModel('mf_shippingrule/rule');
                $model->load($id);
                $model->delete();
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('Rule was successfully removed.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }

        $this->_redirect('*/*');
    }

    public function duplicateAction()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $model = Mage::getModel('mf_shippingrule/rule');
                $model->load($id);
                $model->setId(null);
                $model->save();
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('Rule was successfully duplicated. You can edit it below.')
                );
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }

        $this->_redirect('*/*');
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('mf_shippingrule/rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
  }
}
