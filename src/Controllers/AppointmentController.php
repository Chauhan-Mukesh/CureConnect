// ...existing code...
use CureConnect\Core\Request;
use CureConnect\Core\Response;
use CureConnect\models\Appointment;

class AppointmentController extends Controller
{
// ...existing code...
    public function create(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $appointmentModel = new Appointment();
            $appointmentModel->loadData($request->getBody());
            if ($appointmentModel->validate() && $appointmentModel->save()) {
                $response->redirect('/appointments');
                return;
            }
            return $this->render('appointments/create', [
                'model' => $appointmentModel
            ]);
        }
        return $this->render('appointments/create', [
            'model' => new Appointment()
        ]);
    }

    public function update(Request $request, Response $response)
    {
        $id = $request->getRouteParam('id');
        $appointmentModel = (new Appointment())->findOne(['id' => $id]);

        if (!$appointmentModel) {
            // Handle not found case
            $response->setStatusCode(404);
            return $this->render('_404');
        }

        if ($request->isPost()) {
            $appointmentModel->loadData($request->getBody());
            if ($appointmentModel->validate() && $appointmentModel->update(['id' => $id])) {
                $response->redirect('/appointments');
                return;
            }
        }

        return $this->render('appointments/update', [
            'model' => $appointmentModel
        ]);
    }

    public function delete(Request $request, Response $response)
    {
        $id = $request->getRouteParam('id');
        $appointmentModel = new Appointment();
        if ($request->isPost()) {
            if ($appointmentModel->delete(['id' => $id])) {
                $response->redirect('/appointments');
                return;
            }
        }
        // Optional: render a confirmation page if it's a GET request
        // For now, redirecting or showing error might be enough.
        $response->redirect('/appointments');
    }
}

