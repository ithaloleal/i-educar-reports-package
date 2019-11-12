<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class ServantsHiringAndDismissalReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'servants-hiring-and-dismissal';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('data_inicial');
        $this->addRequiredArg('data_final');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
//        $escola = $this->args['escola'] ?: 0;
        $servidor = $this->args['servidor'] ?: 0;
        $vinculo = $this->args['vinculo'] ?: 0;
        $filtro = $this->args['filtro'] ?: 1;
        $dataInicial = $this->args['data_inicial'] ?: 0;
        $dataFinal = $this->args['data_final'] ?: 0;

        return "select 
distinct cod_servidor,
servidor_funcao.matricula,
nome, 
to_char(servidor_alocacao.data_admissao,'dd/MM/yyyy') as data_admissao, 
to_char(servidor_alocacao.data_saida,'dd/MM/yyyy') as data_saida,
fisica.empresa
from pmieducar.servidor se 
 LEFT JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = se.cod_servidor)
  LEFT JOIN pmieducar.servidor_funcao ON (servidor_funcao.cod_servidor_funcao = servidor_alocacao.ref_cod_servidor_funcao)
left outer join cadastro.pessoa ps on se.cod_servidor = ps.idpes
LEFT JOIN portal.funcionario ON (se.cod_servidor = funcionario.ref_cod_pessoa_fj)
  LEFT JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
  LEFT JOIN cadastro.fisica ON (fisica.idpes = ps.idpes)
WHERE ".
            ($servidor > 0 ? "se.cod_servidor = $servidor AND " : "").
            ($filtro == 1 ? " servidor_alocacao.data_admissao >= '$dataInicial' " : " servidor_alocacao.data_saida >= '$dataInicial' ").
            ($filtro == 1 ? " AND servidor_alocacao.data_admissao <= '$dataFinal' " : " AND servidor_alocacao.data_saida <= '$dataFinal' ")."
             AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE funcionario_vinculo.cod_funcionario_vinculo = {$vinculo} END) 
             order by nome";
    }
}
