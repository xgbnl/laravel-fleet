<?php

declare(strict_types=1);

namespace Xgbnl\Fleet\Controllers;

use Illuminate\Http\JsonResponse;
use Xgbnl\Fleet\Paginator\Paginator;

/**
 * @method JsonResponse json(mixed $data = null, int $code = 200) 自定义Json返回
 * @method JsonResponse triggerValidate(string $message) 触发422表单验证异常
 * @method JsonResponse triggerAuthorization(string $message) 触发401授权异常
 * @method JsonResponse triggerForbidden(string $message) 触发403权限异常
 * @method Paginator customPaginate(array $list = [], bool $isPaginate = true) 自定义分页
 */
abstract class CrudController extends AbstractController
{
    public function index(): JsonResponse
    {
        $models = $this->repository->values($this->request->all());

        $pagesData = $this->customPaginate($models);

        return $this->json($pagesData);
    }

    public function store(): JsonResponse
    {
        if (empty($validated = $this->filter($this->validatedForm()))) {
            $this->triggerValidate('创建的数据不能为空');
        }

        $this->service->createOrUpdate($validated);

        return $this->json('创建成功', 201);
    }

    public function update(): JsonResponse
    {
        return $this->store();
    }

    public function destroy(): JsonResponse
    {
        $this->service->destroy($this->request->input('id'));
        return $this->json('删除成功', 204);
    }
}
