<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/VacancySwingReport.php';

class VacancySwingController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 997903;

    /**
     * @inheritdoc
     */
    protected $_titulo = 'Balanço de vagas';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Balanço de vagas', [
            'educar_servidores_index.php' => 'Servidores',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $opcoes = [1 => 'Acima da capacidade', 2 => 'Abaixo da capacidade', 9 => 'Todas'];
        $this->campoLista('situacao', 'Situação das turmas', $opcoes, 9);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao);

    }

    /**
     * @return VacancySwingReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new VacancySwingReport();
    }
}
