<?php

class UtilityCollection extends Illuminate\Database\Eloquent\Collection
{
	/**
	 * Dynamically retrieve related attributes on the model.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$newCollection = new UtilityCollection();

		foreach ($this->items as $item) {
			if ($item instanceof UtilityCollection) {
				foreach ($item as $subItem) {
					$newCollection->put($newCollection->count(), $subItem->$key);
				}
			}
			elseif (is_object($item) && !$item instanceof UtilityCollection && $item->$key instanceof UtilityCollection) {
				foreach ($item->$key as $subItem) {
					$newCollection->put($newCollection->count(), $subItem);
				}
			}
			else {
				$newCollection->put($newCollection->count(), $item->$key);
			}
		}

		return $newCollection;
	}

	/**
	 * Allow a method to be run on the enitre collection.
	 *
	 * @param string $method
	 * @param array $args
	 * @return UtilityCollection
	 */
	public function __call($method, $args)
	{
		if ($this->count() <= 0) {
			return $this;
		}

		foreach ($this->items as $item) {
			if (!is_object($item)) {
				continue;
			}
			call_user_func_array(array($item, $method), $args);
		}

		return $this;
	}
}
