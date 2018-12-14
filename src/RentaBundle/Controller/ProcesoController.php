<?php

namespace RentaBundle\Controller;


use DateTime;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use RentaBundle\Entity\Formulario;
use RentaBundle\Entity\Interesado;
use RentaBundle\Entity\Proceso;
use RentaBundle\Form\FormularioType;
use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

/**
 * Class ProcesoController
 *
 * @package RentaBundle\Controller
 */
class ProcesoController extends Controller
{
	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function queryAction()
	{
		$EM = $this->getDoctrine()->getManager();
		$ProcesosRepo = $EM->getRepository("RentaBundle:Proceso");
		$Procesos = $ProcesosRepo->findAll();

		return $this->render("query.html.twig", ["Procesos" => $Procesos]);
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function addAction(Request $request)
	{
		$Formulario = new Formulario();
		$fecha = new DateTime();
		$fecha->setDate(date('Y'), date('m'), date('d'));
		$Formulario->setFecha($fecha);
		$FormularioForm = $this->createForm(FormularioType::class, $Formulario);
		$FormularioForm->handleRequest($request);

		if ($FormularioForm->isSubmitted()) {
			$Proceso = $this->addProceso($FormularioForm);
			return $this->render("success.html.twig", array("Proceso" => $Proceso));
		}

		return $this->render("formulario.html.twig", array(
			"form" => $FormularioForm->createView()
		));
	}

	/**
	 * @param \RentaBundle\Form\FormularioType $FormularioForm
	 * @return \RentaBundle\Entity\Proceso
	 */
	public function addProceso(FormularioType $FormularioForm)
	{
		$em = $this->getDoctrine()->getManager();

		$file = $FormularioForm["fichero"]->getData();
		$file_name = $file->getClientOriginalName();
		$file->move("ficheros", $file_name);

		$firma = $FormularioForm["firma"]->getData();
		$firma_name = $firma->getClientOriginalName();
		$firma->move("firmas", $firma_name);


		$Proceso = new Proceso();
		$Proceso->setDescripcion($FormularioForm["descripcion"]->getData());
		$Proceso->setFecha($FormularioForm["fecha"]->getData());
		$Proceso->setFichero($file_name);
		$Proceso->setTexto($FormularioForm["texto"]->getData());
		$Proceso->setFirma($firma_name);
		$Proceso->setFicheroOriginal($file_name);
		$Proceso->setContador(0);
		$Fase = $em->getRepository("RentaBundle:Fase")->find(1);
		$Proceso->setFase($Fase);
		$Organismo = $FormularioForm["organismo"]->getdata();
		$Proceso->setOrganismo($Organismo);

		$em->persist($Proceso);
		$em->flush();

		$FicheroLog = 'logs/procesarMODO1.log';
		$repo = new RotatingFileHandler($FicheroLog, 30, Logger::INFO);
		$log = new Logger('proceso');
		$log->pushHandler($repo);
		$log->info("Proceso: ( " . $Proceso->getId() . ") " . $Proceso->getDescripcion());
		$log->info("Fichero: " . $Proceso->getFichero());
		$log->info("Firma: " . $Proceso->getfirma());
		$log->info("texto: " . $Proceso->getTexto());
		return ($Proceso);
	}

	/**
	 * @param $proceso_id
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
	 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
	 * @throws \setasign\Fpdi\PdfParser\PdfParserException
	 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
	 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
	 */
	public function extraerAction($proceso_id)
	{
		$EM = $this->getDoctrine()->getManager();
		$ProcesoRepo = $EM->getRepository("RentaBundle:Proceso");
		$Proceso = $ProcesoRepo->find($proceso_id);

		if ($Proceso->getFase()->getId() == 3) {
			return $this->render("descarga.html.twig", array("Proceso" => $Proceso));
		}
		switch ($Proceso->getOrganismo()->getModo()) {
			case 1:
				$this->procesarMODO1($Proceso);
				break;
			case 2:
				$this->procesarMODO2($Proceso);
				break;
		}
		$retorno["status"] = 1;
		$response = new Response();
		$response->setContent(json_encode($retorno));
		$response->headers->set("Content-type", "application/json");
		return $response;

	}

	/**
	 * MODO 1 ES EL CERTIFICADO OFICIAL DE LA AEAT CON DOS PÁGINAS AÑO 2017
	 */

	/**
	 * @param \RentaBundle\Entity\Proceso $Proceso
	 * @return \RentaBundle\Entity\Proceso
	 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
	 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
	 * @throws \setasign\Fpdi\PdfParser\PdfParserException
	 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
	 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
	 */
	public function procesarMODO1(Proceso $Proceso)
	{

		$EM = $this->getDoctrine()->getManager();
		$FaseRepo = $EM->getRepository("RentaBundle:Fase");
		if ($Proceso->getFase()->getId() == 1) {
			$cont = $this->extraerNIFMODO1($Proceso);
			$Proceso->setContador($cont);
			$Fase = $FaseRepo->find(2);
			$Proceso->setFase($Fase);
			$EM->persist($Proceso);
			$EM->flush();
		}
		if ($Proceso->getFase()->getId() == 2) {
			$Proceso->setZip($this->generaZIPMODO1($Proceso));
			$Fase = $FaseRepo->find(3);
			$Proceso->setFase($Fase);
			$EM->persist($Proceso);
			$EM->flush();
		}

		return $Proceso;

	}

	/**
	 * @param \RentaBundle\Entity\Proceso $Proceso
	 * @return int
	 * @throws \Exception
	 */
	public function extraerNIFMODO1(Proceso $Proceso)
	{

		$FicheroLog = 'logs/procesarMODO1.log';
		$repo = new RotatingFileHandler($FicheroLog, 30, Logger::INFO);
		$log = new Logger('extraerNIF-1');
		$log->pushHandler($repo);

		$filename = "ficheros/" . $Proceso->getFichero();
		$log->info("Comienza Extracción de los NIF FICHERO: " . $filename);
		$log->info("Comienza parseo del fichero");
		$parser = new Parser($filename);
		$pdf = $parser->parseFile($filename);
		$log->info("Parseo correcto del fichero");
		$pages = $pdf->getPages();
		$log->info("Extracción de paginas correcta");
		$log->info("Eliminación NIF si los hubiera ");
		$this->getDoctrine()->getManager()->getRepository("RentaBundle:Interesado")->deleteInteresados($Proceso->getId());
		//$log->info("NIF elimiandos : " . $contador);

		$pagina = 1;
		$num = 0;
		$log->info("Extracción de NIF");

		// Extraer el NIF de todas los certificados, en el 2017 tiene dos paginas solo tratamos las
		// impares

		foreach ($pages as $page) {
			$text = $page->getText();
			$cadena = "";
			$ct = 10;
			$ctletra = 0;
			for ($i = 11; $i < strlen($text); $i++) {
				$ctletra++;
				if ($ctletra < $ct) {
					$cadena .= $text[$i];
				}
			}
			if ($pagina == 1) {
				$num++;
				$Interesado = new Interesado();
				$Interesado->setNif($cadena);
				$Interesado->setProceso($Proceso);
				$this->getDoctrine()->getManager()->persist($Interesado);
				$this->getDoctrine()->getManager()->flush();
				$pagina = 0;
			} else {
				$pagina = 1;
			}

		}
		$log->info("Extracción de NIF certificados a generar: " . $num);
		return $num;
	}

	/**
	 * @param \RentaBundle\Entity\Proceso $Proceso
	 * @return string
	 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
	 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
	 * @throws \setasign\Fpdi\PdfParser\PdfParserException
	 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
	 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
	 */
	public function generaZIPMODO1(Proceso $Proceso)
	{
		$EM = $this->getDoctrine()->getManager();
		$InteresadoRepo = $EM->getRepository("RentaBundle:Interesado");
		$Interesados = $InteresadoRepo->interesadosByProceso($Proceso->getId());

		$FicheroLog = 'logs/procesarMODO1.log';
		$repo = new RotatingFileHandler($FicheroLog, 30, Logger::INFO);
		$log = new Logger('generarZIP');
		$log->pushHandler($repo);

		$filename = "ficheros/" . $Proceso->getFichero();
		$firmaname = "firmas/" . $Proceso->getFirma();
		$log->info("Generado ficheros ZIP");
		$zip = new ZipArchive();
		$zip_name = "zip/" . time() . ".zip";
		$zip_sinpath = time() . ".zip";
		$zip->open($zip_name, ZipArchive::CREATE);
		$log->info("Generado ficheros ZIP : " . $zip_sinpath);
		$path = "tmp/proceso" . $Proceso->getId();

		if (file_exists($path)) {
			$this->eliminarDir($path);
			$log->info('Directorio :' . $path . ' ya existe, se elimina ');
		}
		mkdir($path, 777);
		$pagina = 1;
		foreach ($Interesados as $registro) {
			$Interesado = $InteresadoRepo->find($registro["id"]);
			$fichero = new Fpdi();
			$fichero->setSourceFile($filename);
			$tplIdx = $fichero->importPage($pagina, '/MediaBox');
			$fichero->AddPage();
			$fichero->useTemplate($tplIdx);

			$pagina++;
			$tplIdx1 = $fichero->importPage($pagina, '/MediaBox');
			$fichero->AddPage();
			$fichero->useTemplate($tplIdx1);
			$fichero->Image($firmaname, 85, 275, 35, 16);
			$nombre = $path . "/00_" . $Interesado->getNif() . '_' . $Proceso->getTexto() . '.pdf';
			$sinDir = "00_" . $Interesado->getNif() . '_' . $Proceso->getTexto() . '.pdf';
			$Interesado->setFichero($sinDir);
			$EM->persist($Interesado);
			$EM->flush();

			$fichero->output("F", $nombre);
			$zip->addFile($nombre, $sinDir);
		}

		$log->info("PROCESO FINALIZADO: ");
		$zip->close();
		return $zip_sinpath;
	}

	/**
	 * MODO 2 CERTIFICADO ESPECIFICO PARA EL HOSPITAL UNIVERSITARIO DE LA PAZ, SOLO UNA PÁGINA
	 *
	 * @param \RentaBundle\Entity\Proceso $Proceso
	 * @return \RentaBundle\Entity\Proceso
	 * @throws \Exception
	 */
	public function procesarMODO2(Proceso $Proceso)
	{
		$EM = $this->getDoctrine()->getManager();
		$FaseRepo = $EM->getRepository("RentaBundle:Fase");
		if ($Proceso->getFase()->getId() == 1) {
			$cont = $this->extraerNIFMODO2($Proceso);
			$Proceso->setContador($cont);
			$Fase = $FaseRepo->find(2);
			$Proceso->setFase($Fase);
			$EM->persist($Proceso);
			$EM->flush();
		}
		if ($Proceso->getFase()->getId() == 2) {
			$Proceso->setZip($this->generaZIPMODO2($Proceso));
			$Fase = $FaseRepo->find(3);
			$Proceso->setFase($Fase);
			$EM->persist($Proceso);
			$EM->flush();
		}
		return $Proceso;
	}

	/**
	 * @param \RentaBundle\Entity\Proceso $Proceso
	 * @return int
	 * @throws \Exception
	 */
	public function extraerNIFMODO2(Proceso $Proceso)
	{
		$EM = $this->getDoctrine()->getManager();
		$InteresadoRepo = $EM->getRepository("RentaBundle:Interesado");

		$FicheroLog = 'logs/procesarMODO2.log';
		$repo = new RotatingFileHandler($FicheroLog, 30, Logger::INFO);
		$log = new Logger('extraerNIF');
		$log->pushHandler($repo);

		$filename = "ficheros/" . $Proceso->getFichero();
		$log->info("Comienza Extracción de los NIF FICHERO: " . $filename);

		$parser = new Parser();
		$log->info("Comienza parseo del fichero");
		$pdf = $parser->parseFile($filename);
		$log->info("Parseo correcto del fichero");
		$pages = $pdf->getPages();
		$log->info("Extracción de paginas correcta");
		$log->info("Elminación NIF si los hubiera ");
		$contador = $InteresadoRepo->deleteInteresados($Proceso->getId());
		$log->info("NIF elimiandos : " . $contador);

		$num = 0;
		$log->info("Extracción de NIF");
		$patron = 'N.I.F.';
		/*
		 * la extracción del NIF empieza en la posición a donde encontramos el patron N.I.F. + 6 espacios en blanco
		 * ya que es una cadena de longitud variable
		 */
		foreach ($pages as $page) {
			$text = $page->getText();
			$cadena = "";
			$ct = 10;
			$ctletra = 0;
			$inicio = strpos($text, $patron) + 7;
			for ($i = $inicio; $i < strlen($text); $i++) {
				$ctletra++;
				if ($ctletra < $ct) {
					$cadena .= $text[$i];
				}
			}

			$num++;
			$Interesado = new Interesado();
			$Interesado->setNif($cadena);
			$Interesado->setProceso($Proceso);
			$EM->persist($Interesado);
			$EM->flush();
		}

		$log->info("Extracción de NIF certificados a generar: " . $num);
		return $num;
	}

	/**
	 * @param \RentaBundle\Entity\Proceso $Proceso
	 * @return string
	 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
	 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
	 * @throws \setasign\Fpdi\PdfParser\PdfParserException
	 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
	 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
	 */
	public function generaZIPMODO2(Proceso $Proceso)
	{
		$EM = $this->getDoctrine()->getManager();
		$InteresadoRepo = $EM->getRepository("RentaBundle:Interesado");
		$Interesados = $InteresadoRepo->interesadosByProceso($Proceso->getId());

		$FicheroLog = 'logs/procesarMODO2.log';
		$repo = new RotatingFileHandler($FicheroLog, 30, Logger::INFO);
		$log = new Logger('generarZIP');
		$log->pushHandler($repo);

		$filename = "ficheros/" . $Proceso->getFichero();
		$firmaname = "firmas/" . $Proceso->getFirma();
		$log->info("Generado ficheros ZIP");
		$zip = new \ZipArchive();
		$zip_name = "zip/" . time() . ".zip";
		$zip_sinpath = time() . ".zip";
		$zip->open($zip_name, ZipArchive::CREATE);
		$log->info("Generado ficheros ZIP : " . $zip_sinpath);
		$path = "tmp/proceso" . $Proceso->getId();
		if (file_exists($path)) {
			$this->eliminarDir($path);
			$log->info('Directorio :' . $path . ' ya existe, se elimina ');
		}
		mkdir($path, 777);

		$pagina = 1;
		foreach ($Interesados as $registro) {
			$Interesado = $InteresadoRepo->find($registro["id"]);
			$fichero = new Fpdi();
			$fichero->setSourceFile($filename);
			$tplIdx = $fichero->importPage($pagina, '/MediaBox');
			$fichero->AddPage();
			$fichero->useTemplate($tplIdx);
			$fichero->Image($firmaname, 85, 275, 35, 16);
			$nombre = $path . "/00_" . $Interesado->getNif() . '_' . $Proceso->getTexto() . '.pdf';
			$sinDir = "00_" . $Interesado->getNif() . '_' . $Proceso->getTexto() . '.pdf';
			$Interesado->setFichero($sinDir);
			$EM->persist($Interesado);
			$EM->flush();

			$fichero->output("F", $nombre);

			$zip->addFile($nombre, $sinDir);
			$pagina++;
		}

		$log->info("PROCESO FINALIZADO: ");
		$zip->close();
		return $zip_sinpath;
	}

	/**
	 * @param $proceso_id
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function descargarAction($proceso_id)
	{
		$EM = $this->getDoctrine()->getManager();
		$ProcesoRepo = $EM->getRepository("RentaBundle:Proceso");
		$Proceso = $ProcesoRepo->find($proceso_id);

		$filename = 'zip/' . $Proceso->getZip();

		return $this->file($filename);
	}

	/**
	 * @param $proceso_id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function editAction($proceso_id)
	{
		$EM = $this->getDoctrine()->getManager();
		$ProcesoRepo = $EM->getRepository("RentaBundle:Proceso");
		$Proceso = $ProcesoRepo->find($proceso_id);

		if ($Proceso->getFase()->getId() == 3) {
			return $this->render("descarga.html.twig", array("Proceso" => $Proceso));
		} else {

			return $this->render("success.html.twig", array("Proceso" => $Proceso));
		}
	}

	/**
	 * @param $carpeta
	 */
	public function eliminarDir($carpeta)
	{
		foreach (glob($carpeta . "/*") as $archivos_carpeta) {
			if (is_dir($archivos_carpeta)) {
				$this->eliminarDir($archivos_carpeta);
			} else {
				unlink($archivos_carpeta);
			}
		}
		rmdir($carpeta);
	}

	/**
	 * @param $proceso_id
	 */
	public function deleteAction($proceso_id)
	{
		$EM = $this->getDoctrine()->getManager();
		$ProcesoRepo = $EM->getRepository("RentaBundle:Proceso");
		$Proceso = $ProcesoRepo->find($proceso_id);

		$EM->remove($Proceso);
		$EM->flush();
		$path = "tmp\proceso" . $Proceso->getId();

		$FicheroLog = 'logs/procesarMODO1.log';
		$repo = new RotatingFileHandler($FicheroLog, 30, Logger::INFO);
		$log = new Logger('generarZIP');
		$log->pushHandler($repo);

		if (file_exists($path)) {
			$this->eliminarDir($path);
			$log->info('Directorio :' . $path . ' ya existe, se elimina ');
		}
	}
}
